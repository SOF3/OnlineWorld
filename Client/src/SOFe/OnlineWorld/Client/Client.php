<?php

/*
 * OnlineWorld-Client
 *
 * Copyright (C) 2018 SOFe
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace SOFe\OnlineWorld\Client;

use function assert;
use pocketmine\Thread;
use pocketmine\utils\Binary;
use RuntimeException;
use SOFe\OnlineWorld\Client\Packet\In\AuthSuccessPacket;
use SOFe\OnlineWorld\Client\Packet\In\IncomingPacket;
use SOFe\OnlineWorld\Client\Packet\In\PingPacket;
use SOFe\OnlineWorld\Client\Packet\In\ServerChallengePacket;
use SOFe\OnlineWorld\Client\Packet\Out\AuthPacket;
use SOFe\OnlineWorld\Client\Packet\Out\ClientChallengePacket;
use SOFe\OnlineWorld\Client\Packet\Out\ClosePacket;
use SOFe\OnlineWorld\Client\Packet\Out\OutgoingPacket;
use SOFe\OnlineWorld\Client\Packet\Out\PongPacket;
use function substr;
use Threaded;
use const AF_INET;
use const SOCK_STREAM;
use const SOL_TCP;
use function random_bytes;
use function sha1;
use function socket_connect;
use function socket_create;
use function socket_read;
use function socket_set_block;
use function socket_set_nonblock;
use function socket_write;
use function strlen;

class Client extends Thread{
	public $running = true;
	/** @var Threaded */
	public $packetsRecv;
	/** @var Threaded */
	public $packetsSend;

	/** @var string */
	private $host;
	/** @var int */
	private $port;
	/** @var string */
	private $magicSalt;
	/** @var string */
	private $username;
	/** @var string */
	private $password;
	/** @var string */
	private $worldName;

	private $buffer = "";
	/** @var AuthSuccessPacket|null */
	private $serverSetup;

	public function __construct(string $host, int $port, string $magicSalt, string $username, string $password, string $worldName){
		$this->host = $host;
		$this->port = $port;
		$this->magicSalt = $magicSalt;
		$this->username = $username;
		$this->password = $password;
		$this->worldName = $worldName;

		$this->packetsRecv = new Threaded();
		$this->packetsSend = new Threaded();

		$this->start();
	}

	public function run() : void{
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($socket, $this->host, $this->port);
		socket_set_block($socket);

		$packet = new ClientChallengePacket();
		$packet->challenge = $challenge = random_bytes(16);
		self::sendPacket($socket, $packet);
		$serverChallenge = self::receivePacket($socket, $this->buffer);
		if(!($serverChallenge instanceof ServerChallengePacket) || $serverChallenge->clientResponse !== sha1($challenge . $this->magicSalt, true)){
			throw new RuntimeException("Server failed to respond with client challenge! Check your host, port and magicSalt.");
		}

		$packet = new AuthPacket();
		$packet->username = $this->username;
		$packet->password = sha1($serverChallenge->serverChallenge . $this->password, true);
		$packet->worldName = $this->worldName;
		self::sendPacket($socket, $packet);

		$packet = self::receivePacket($socket, $this->buffer);
		if(!($packet instanceof AuthSuccessPacket)){
			throw new RuntimeException("Failed to authenticate in server");
		}
		$this->serverSetup = $packet;

		socket_set_nonblock($socket);
		while($this->running){
			$packet = self::receivePacket($socket, $this->buffer);
			if($packet !== null){
				if($packet instanceof PingPacket){
					$pong = new PongPacket();
					$pong->pingNumber = $packet->pingNumber;
					self::sendPacket($socket, $pong);
					$this->packetsRecv[] = $packet;
				}
				while(($pk = $this->packetsSend->shift()) !== null){
					self::sendPacket($socket, $pk);
				}
			}
		}

		self::sendPacket($socket, new ClosePacket());
	}

	public function getServerSetup() : ?AuthSuccessPacket{
		return $this->serverSetup;
	}

	public static function sendPacket($socket, OutgoingPacket $packet) : void{
		$stream = new MyBinaryStream();
		$packet->writeBuffer($stream);
		socket_write($socket, Binary::writeShort($packet->getId()) . Binary::writeInt(strlen($stream->buffer)));
		socket_write($socket, $stream->buffer);
	}

	public static function receivePacket($socket, string &$buffer) : ?IncomingPacket{
		$id = Binary::readShort(self::readSocket($socket, 2, $buffer));
		if(!isset(IncomingPacket::CLASSES[$id])){
			throw new RuntimeException("Server returned unknown packet");
		}
		$length = Binary::readInt(self::readSocket($socket, 4, $buffer));
		/** @noinspection UnnecessaryParenthesesInspection
		 * @var IncomingPacket $packet
		 */
		$packet = new (IncomingPacket::CLASSES[$id])();
		$packet->readBuffer(new MyBinaryStream(self::readSocket($socket, $length, $buffer)));
		return $packet;
	}

	public static function readSocket($socket, int $bytes, string &$buffer): ?string{
		$read = $bytes - strlen($buffer);
		if($read < 0){
			$ret = substr($buffer, 0, $bytes);
			$buffer = (string) substr($buffer, $bytes);
			return $ret;
		}

		if($read === 0){
			$ret = $buffer;
			$buffer = "";
			return $ret;
		}

		$in = socket_read($socket, $read);
		if($in === false || strlen($in) < $read){
			$buffer .= $in;
			return null;
		}
		assert($read === strlen($in));
		$ret = $buffer . $in;
		$buffer = "";
		return $ret;
	}
}
