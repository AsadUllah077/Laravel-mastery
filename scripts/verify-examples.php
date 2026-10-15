<?php

// Only runs repository-authored, explicitly standalone curriculum fixtures from the CLI.
// This is never exposed through a route and never accepts browser-supplied PHP.
require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Process\Process;

$count = 0;
$failures = 0;
foreach (glob(dirname(__DIR__).'/database/content/*.json') as $file) {
    foreach (json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR) as $lesson) {
        if (($lesson['runnable'] ?? true) === false || ($lesson['language'] ?? 'php') !== 'php') {
            continue;
        }
        $temporary = tempnam(dirname(__DIR__).'/storage/framework', 'academy-example-');
        try {
            file_put_contents($temporary, $lesson['code']);
            $process = new Process([PHP_BINARY, $temporary]);
            $process->setTimeout(5);
            $process->run();
            $normalize = fn ($value) => trim(str_replace("\r\n", "\n", $value));
            if (! $process->isSuccessful() || $normalize($process->getOutput()) !== $normalize($lesson['output'])) {
                $failures++;
                echo 'FAIL: '.$lesson['title']."\n".$process->getErrorOutput()."\n";
            }
            $count++;
        } finally {
            unlink($temporary);
        }
    }
}
echo "$count standalone examples verified; $failures failures. Framework snippets and blueprints are not executed.\n";
exit($failures ? 1 : 0);
