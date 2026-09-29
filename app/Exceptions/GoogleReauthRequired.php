<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A user refresh tokenje hiányzik vagy visszavonták – újra be kell lépnie Google-lel.
 */
class GoogleReauthRequired extends RuntimeException {}
