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

class PingPacket implements IncomingPacket{
	public $pingNumber;
	public $levelTime;

	public function getId() : int{
		return IncomingPacket::OW_PING;
	}

	public function readBuffer(MyBinaryStream $stream) : void{
		$this->pingNumber = $stream->getLong();
		$this->levelTime = $stream->getLong();
	}
}
