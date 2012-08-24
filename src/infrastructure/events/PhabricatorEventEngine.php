<?php

/*
 * Copyright 2012 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @group events
 */
final class PhabricatorEventEngine {

  public static function initialize() {
    $listeners = PhabricatorEnv::getEnvConfig('events.listeners');
    foreach ($listeners as $listener) {
      id(new $listener())->register();
    }

    // Register the DarkConosole event logger.
    id(new DarkConsoleEventPluginAPI())->register();
    id(new ManiphestEdgeEventListener())->register();

    $applications = PhabricatorApplication::getAllInstalledApplications();
    foreach ($applications as $application) {
      $listeners = $application->getEventListeners();
      foreach ($listeners as $listener) {
        $listener->register();
      }
    }

  }

}
