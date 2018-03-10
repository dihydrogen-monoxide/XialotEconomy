<?php

/**
 * XialotEcon
 *
 * Copyright (C) 2017-2018 dihydrogen-monoxide
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

declare(strict_types=1);

namespace DHMO\XialotEcon\Provider;

use pocketmine\utils\UUID;

class ProvidedDatumMap{
	/** @var DataProvider */
	private $provider;
	/** @var ProvidedDatum[] */
	private $store = [];

	public function __construct(DataProvider $provider){
		$this->provider = $provider;
	}

	/**
	 * @return DataProvider
	 */
	public function getProvider() : DataProvider{
		return $this->provider;
	}

	public function onDatumLoaded(ProvidedDatum $datum) : void{
		$this->store[$datum->uuid->toString()] = $datum;
	}

	public function onUpdate(UUID $uuid) : void{
		if(isset($this->store[$str = $uuid->toString()])){
			$this->store[$str]->setOutdated();
		}
	}

	public function doCycle() : void{
		foreach($this->store as $uuid => $datum){
			if($datum->shouldStore()){
				$datum->store();
			}
			if($datum->isGarbage()){
				unset($this->store[$uuid]);
			}
		}
	}
}
