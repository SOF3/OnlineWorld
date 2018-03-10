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

class AuthSuccessPacket implements IncomingPacket{
	/** @var int */
	public $worldHeight;
	/** @var int */
	public $seed;
	/** @var int */
	public $currentTime;
	/** @var float */
	public $timeTickRate;

	public function getId() : int{
		return self::OW_AUTH_SUCCESS;
	}

	public function readBuffer(MyBinaryStream $stream) : void{
		$this->worldHeight = $stream->getInt();
		$this->seed = $stream->getLong();
		$this->currentTime = $stream->getLong();
		$this->timeTickRate = $stream->getFloat();
	}
}
