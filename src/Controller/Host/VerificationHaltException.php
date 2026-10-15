<?php
declare(strict_types=1);

namespace App\Controller\Host;

use Cake\Http\Response;

/**
 * Carries a ready-made error response out of the upload closure, so JSON
 * form submissions get the real failure reason instead of an HTML error.
 */
class VerificationHaltException extends \RuntimeException
{
    public Response $response;

    public function __construct(Response $response)
    {
        parent::__construct('verification halt');
        $this->response = $response;
    }
}
