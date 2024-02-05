
const fs = require('fs');
const dotenv = require('dotenv');

if (!('DOTENV_KEY' in process.env)) {
    console.log("Missing environment variable: DOTENV_KEY");
    process.exit(1);
}

if (!process.argv[2]) {
    console.log("Missing parameter: path to input .env.vault file");
    process.exit(1);
}

if (!process.argv[3]) {
    console.log("Missing parameter: path to output .env file");
    process.exit(1);
}

const output = dotenv.config({
    path: process.argv[2],
    processEnv: {}
});

if (output.error) {
    console.log("Failed to decrypt .env.vault file");
    console.log(output.error);
    process.exit(1);
}

const writer = fs.createWriteStream(process.argv[3]);
const writeLine = (line) => writer.write(`${line}\n`);
for (const [key, value] of Object.entries(output.parsed)) {
    writeLine(`${key}="${value}"`);
}
writer.close();

console.log("Successfully decrypted .env.vault file");
