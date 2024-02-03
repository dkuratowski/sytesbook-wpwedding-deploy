
const fs = require('fs');
const dotenv = require('dotenv');

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

const decryptedDotEnv = JSON.stringify(output.parsed, null, 2);
fs.writeFileSync(process.argv[3], decryptedDotEnv);

console.log("Successfully decrypted .env.vault file");
