<?php
/**
 * @todo maybe make objects instead of array for parameters for final endpoint functions
 * @todo make transaction_key more easier to track
 * @todo refactor NetM Emulator
 * @todo add post processor for responses so it can convert if needed
 * @todo improve creation of url in emulator classes
 * @todo clean up
 * @todo check code
 * @todo resolve issue with assets
 * @todo add flow control for every step
 * @todo improve js
 * @todo make option to turn on|off debugger
 * @todo add docker production|development
 * @todo solve iframe problem
 */
require_once __DIR__ . '/../vendor/autoload.php';

(new Gemu\Core\Application())->run();
