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

class SetLongPacket implements OutgoingPacket{
	public const WHICH_TIME = 1;
	public const WHICH_SEED = 2;

	/** @var int */
	public $which;
	/** @var int */
	public $value;

	public function getId() : int{
		return OutgoingPacket::OW_SET_LONG;
	}

	public function writeBuffer(MyBinaryStream $stream) : void{
		$stream->putInt($this->which);
		$stream->putLong($this->value);
	}
}
