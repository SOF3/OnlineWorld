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

use Logger;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\level\LevelException;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\utils\MainLogger;
use SOFe\OnlineWorld\Client\Packet\In\IncomingPacket;
use SOFe\OnlineWorld\Client\Packet\Out\OutgoingPacket;
use function is_dir;
use function is_file;
use function microtime;
use SOFe\OnlineWorld\Client\Packet\Out\SetLongPacket;
use function yaml_parse_file;

class OnlineLevelProvider extends PluginTask implements LevelProvider{
	/** @var string */
	private $directory;
	/** @var ConfigFile */
	private $config;
	/** @var Logger */
	private $logger;

	/** @var Client */
	private $conn;

	// time = time0 + (clk - clk0) * f
	private $timeTime0;
	private $timeClk0;
	private $timeF;

	public function __construct(string $path){
		$this->directory = $path;
		$configFile = $path . "onlineWorld.yml";
		if(!is_dir($this->directory) || !is_file($configFile)){
			throw new LevelException("\"$path\" does not contain an OnlineWorld config. Use the command \"/ow create\" to create one.");
		}

		$this->config = new ConfigFile(yaml_parse_file($configFile));
		$this->logger = MainLogger::getLogger();

		$this->logger->info("Connecting to {$this->config->host}:{$this->config->port}");
		$this->conn = $this->config->connect();
		while(($setup = $this->conn->getServerSetup()) === null){
			usleep(1000); // wait for 1 ms, then check if thread has received packet
		}
		$this->logger->debug("Connected to {$this->config->host}:{$this->config->port}: worldHeight = {$setup->worldHeight}, seed = {$setup->seed}");
		$this->timeClk0 = microtime(true);
		$this->timeTime0 = $setup->currentTime;
		$this->timeF = $setup->timeTickRate;

		$main = Main::getInstance();
		$main->getServer()->getScheduler()->scheduleRepeatingTask($this, 1);
	}

	public static function getProviderName() : string{
		return "onlineworld";
	}

	public function getWorldHeight() : int{
		return $this->conn->getServerSetup()->worldHeight;
	}

	public function getPath() : string{
		return $this->directory;
	}

	public static function isValid(string $path) : bool{
		return is_file($path . "onlineWorld.yml");
	}

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []){
	}

	public function getGenerator() : string{
		return "online_world";
	}

	public function getGeneratorOptions() : array{
		return [];
	}

	public function saveChunk(Chunk $chunk) : void{
	}

	/**
	 * Loads a chunk (usually from disk storage) and returns it. If the chunk does not exist, null is returned.
	 *
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return null|Chunk
	 *
	 * @throws \Exception any of a range of exceptions that could be thrown while reading chunks. See individual
	 * implementations for details.
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?Chunk{
		// TODO: Implement loadChunk() method.
	}

	public function getName() : string{
		return $this->config->worldName;
	}

	/**
	 * @return int
	 */
	public function getTime() : int{
		return (microtime(true) - $this->timeClk0) * $this->timeF + $this->timeTime0;
	}

	/**
	 * @param int
	 */
	public function setTime(int $value){

	}

	/**
	 * @return int
	 */
	public function getSeed() : int{
		return $this->conn->getServerSetup()->seed;
	}

	/**
	 * @param int
	 */
	public function setSeed(int $value){
		$packet = new SetLongPacket();
		$packet->which = SetLongPacket::WHICH_SEED;
		$packet->value = $value;
		$this->sendServerPacket($packet);
	}

	/**
	 * @return Vector3
	 */
	public function getSpawn() : Vector3{
		// TODO: Implement getSpawn() method.
	}

	/**
	 * @param Vector3 $pos
	 */
	public function setSpawn(Vector3 $pos){
		// TODO: Implement setSpawn() method.
	}

	/**
	 * Returns the world difficulty. This will be one of the Level constants.
	 * @return int
	 */
	public function getDifficulty() : int{
		// TODO: Implement getDifficulty() method.
	}

	/**
	 * Sets the world difficulty.
	 * @param int $difficulty
	 */
	public function setDifficulty(int $difficulty){
		// TODO: Implement setDifficulty() method.
	}

	/**
	 * Performs garbage collection in the level provider, such as cleaning up regions in Region-based worlds.
	 */
	public function doGarbageCollection(){
		// TODO: Implement doGarbageCollection() method.
	}

	/**
	 * Performs cleanups necessary when the level provider is closed and no longer needed.
	 */
	public function close(){
		// TODO: Implement close() method.
	}

	public function onRun(int $currentTick){
		/** @var IncomingPacket $packet */
		while(($packet = $this->conn->packetsRecv->shift()) !== null){
			$this->handlePacket($packet);
		}
	}


	public function sendServerPacket(OutgoingPacket $packet) : void{
		$this->conn->packetsSend[] = $packet;
	}

	private function handlePacket(IncomingPacket $packet) : void{
	}
}
