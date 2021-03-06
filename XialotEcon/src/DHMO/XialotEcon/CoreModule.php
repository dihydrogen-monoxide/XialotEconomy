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

namespace DHMO\XialotEcon;

use DHMO\XialotEcon\Database\DutyManager;
use DHMO\XialotEcon\Database\Queries;
use DHMO\XialotEcon\Init\InitGraph;
use DHMO\XialotEcon\Util\StringUtil;
use poggit\libasynql\CallbackTask;
use const INF;

final class CoreModule extends XialotEconModule{
	protected static function getName() : string{
		return "core";
	}

	protected static function shouldConstruct(XialotEcon $plugin) : bool{
		return true;
	}

	public function __construct(XialotEcon $plugin, InitGraph $graph){
		$this->plugin = $plugin;
		if($plugin->getInitMode() === XialotEcon::INIT_INIT){
			$graph->addGenericQuery("core.feed.init", Queries::XIALOTECON_DATA_MODEL_FEED_INIT);
			$graph->addGenericQuery("core.duty.init.table", Queries::XIALOTECON_DATA_MODEL_DUTY_INIT_TABLE);
			$graph->addGenericQuery("core.duty.init.function", Queries::XIALOTECON_DATA_MODEL_DUTY_INIT_FUNC, [], "core.duty.init.table");
		}
		$graph->add("core.feed.fetch_first", [$this->plugin->getModelCache(), "scheduleUpdate"],
			$plugin->getInitMode() === XialotEcon::INIT_INIT ? ["core.feed.init"] : []);
	}

	public function onStartup() : void{
		$this->plugin->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this->plugin->getModelCache(), "doCycle"]), 20);

		$persistTime = StringUtil::parseTime($this->plugin->getConfig()->get("data-model")["feed-persistence"]);
		if($persistTime < INF && $persistTime >= 0){
			$this->plugin->getScheduler()->scheduleRepeatingTask(new CallbackTask(function() use ($persistTime){
				$this->plugin->getConnector()->executeChange(Queries::XIALOTECON_DATA_MODEL_FEED_CLEAR, ["persistence" => $persistTime]);
			}), 600);
		}

		$this->plugin->getScheduler()->scheduleRepeatingTask(new DutyManager($this->plugin), 100);
	}
}
