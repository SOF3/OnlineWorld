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

namespace SOFe\OnlineWorld\Client\Packet\Out;

use SOFe\OnlineWorld\Client\MyBinaryStream;

interface OutgoingPacket{
	public const OW_CLIENT_CHALLENGE = 0x1;
	public const OW_AUTH = 0x2;
	public const OW_CLOSE = 0x3;
	public const OW_PONG = 0x4;
	public const OW_SET_LONG = 0x5;

	public function getId() : int;

	public function writeBuffer(MyBinaryStream $stream) : void;
}
