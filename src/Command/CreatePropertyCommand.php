<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\FastnetApiClient;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;

class CreatePropertyCommand extends Command
{
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->addArgument('payload', ['help' => 'Base64 encoded JSON payload', 'required' => true]);
        $parser->addArgument('headers', ['help' => 'Base64 encoded JSON headers', 'required' => true]);
        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $payload = json_decode(base64_decode($args->getArgument('payload')), true);
        $headers = json_decode(base64_decode($args->getArgument('headers')), true);

        if (!$payload || !$headers) {
            $io->error('Invalid payload or headers');
            return static::CODE_ERROR;
        }

        $api = new FastnetApiClient();
        $res = $api->post('/properties', $payload, $headers);
        
        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
            Log::error('Background CreateProperty failed: ' . json_encode($res));
            return static::CODE_ERROR;
        }

        $pid = (int)(($res['id'] ?? $res['data']['id'] ?? 0));
        if ($pid > 0) {
            try {
                $api->post('/verification/lodge/' . $pid, [], $headers);
            } catch (\Throwable $e) {
                // Non-fatal
            }
        }

        $io->success('Created property ' . $pid);
        return static::CODE_SUCCESS;
    }
}
