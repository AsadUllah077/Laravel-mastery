// Use the npm CLI next to Node without relying on a misconfigured npm shell launcher.
const { spawnSync } = require('node:child_process');
const { dirname, join } = require('node:path');
const { existsSync } = require('node:fs');
const cli = join(dirname(process.execPath), 'node_modules', 'npm', 'bin', 'npm-cli.js');
if (!existsSync(cli)) {
    console.error('npm CLI was not found next to Node. Use your working npm installation directly.');
    process.exit(1);
}
const result = spawnSync(process.execPath, [cli, ...process.argv.slice(2)], { stdio: 'inherit' });
if (result.error) console.error(result.error.message);
process.exit(result.status ?? 1);
