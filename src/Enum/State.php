<?php
namespace App\Enum;

use MyCLabs\Enum\Enum;

final class State extends Enum
{
    const SAVE = 0;
    const SENDING = 1;
    const READ = 2;
    const ACCPETED = 3;
    const FINISH = 4;
    const REJECTED = -1;
}