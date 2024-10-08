const axios = require('axios');

if (!('OPERATOR_USERNAME' in process.env)) {
    console.log("Missing environment variable: OPERATOR_USERNAME");
    process.exit(1);
}

if (!('OPERATOR_PASSWORD' in process.env)) {
    console.log("Missing environment variable: OPERATOR_PASSWORD");
    process.exit(1);
}

if (!process.argv[2]) {
    console.log("Missing parameter: model collection");
    process.exit(1);
}

if (!process.argv[3]) {
    console.log("Missing parameter: model UID");
    process.exit(1);
}

const username = process.env['OPERATOR_USERNAME'];
const password = process.env['OPERATOR_PASSWORD'];
const modelCollection = process.argv[2];
const modelUid = process.argv[3];
const deletionTime = process.argv[4] ?? null;

let requestBody = null;
if (deletionTime === null) {
    requestBody = {
        data: {
            deletedAt: null
        }
    };
}
else if (deletionTime !== 'now') {
    requestBody = {
        data: {
            deletedAt: deletionTime
        }
    };
}

console.log('Sending request to /admin/soft-delete:', requestBody);

const config = {
    auth: { username: username, password: password },
    headers: { 'X-HTTP-Method-Override': 'PUT' }
};

axios.post(
    `https://${mainDomainName}/wp-json/wpwedding/v1/${modelCollection}/${modelUid}/admin/soft-delete`,
    requestBody,
    config
).then(response => {
    console.log('Response');
    console.log(response);
    process.exit(0);
}).catch(err => {
    console.log('Error');
    console.log(err);
    process.exit(1);
});

