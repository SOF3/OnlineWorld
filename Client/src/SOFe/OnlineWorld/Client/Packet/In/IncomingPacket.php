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

namespace SOFe\OnlineWorld\Client\Packet\In;

use SOFe\OnlineWorld\Client\MyBinaryStream;

interface IncomingPacket{
	public const OW_SERVER_CHALLENGE = 0x8001;
	public const OW_AUTH_SUCCESS = 0x8002;
	public const OW_KICK = 0x8003;
	public const OW_PING = 0x8004;

	public const CLASSES = [
		self::OW_SERVER_CHALLENGE => ServerChallengePacket::class,
		self::OW_AUTH_SUCCESS => AuthSuccessPacket::class,
		self::OW_KICK => KickPacket::class,
		self::OW_PING => PingPacket::class,
	];

	public function getId() : int;

	public function readBuffer(MyBinaryStream $stream) : void;
}
