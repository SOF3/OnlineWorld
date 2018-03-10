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

use function sha1;

class ConfigFile{
	public $host;
	public $port;
	public $magicSalt;
	public $username;
	public $password;
	public $worldName;

	public function __construct(array $data){
		$this->host = (string) $data["host"];
		$this->port = (int) $data["port"];
		$this->magicSalt = (string) $data["magicSalt"];
		$this->username = (string) $data["username"];
		$this->password = sha1((string) $data["password"], true);
		$this->worldName = (string) $data["worldName"];
	}

	public function connect() : Client{
		return new Client($this->host, $this->port, $this->magicSalt, $this->username, $this->password, $this->worldName);
	}
}
