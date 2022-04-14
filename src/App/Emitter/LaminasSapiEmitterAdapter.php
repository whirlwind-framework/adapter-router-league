<?php

declare(strict_types=1);

namespace Whirlwind\Adapter\League\App\Emitter;

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Whirlwind\App\Emitter\EmitterInterface;

class LaminasSapiEmitterAdapter extends SapiEmitter implements EmitterInterface
{
}
