if (!('OPERATOR_USERNAME' in process.env)) {
    console.log("Missing environment variable: OPERATOR_USERNAME");
    process.exit(1);
}

if (!('OPERATOR_PASSWORD' in process.env)) {
    console.log("Missing environment variable: OPERATOR_PASSWORD");
    process.exit(1);
}

if (!process.argv[2]) {
    console.log("Missing parameter: main domain name");
    process.exit(1);
}

if (!process.argv[3]) {
    console.log("Missing parameter: model collection");
    process.exit(1);
}

if (!process.argv[4]) {
    console.log("Missing parameter: model UID");
    process.exit(1);
}

const username = process.env['OPERATOR_USERNAME'];
const password = process.env['OPERATOR_PASSWORD'];
const mainDomainName = process.argv[2];
const modelCollection = process.argv[3];
const modelUid = process.argv[4];
const deletionTime = process.argv[5] ?? null;

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

async function sendRequest() {

    const requestBodyStr = JSON.stringify(requestBody);
    console.log('Sending request to /admin/soft-delete:', requestBodyStr);

    try {
        const response = await fetch(`https://${mainDomainName}/wp-json/wpwedding/v1/${modelCollection}/${modelUid}/admin/soft-delete`, {
            method: 'POST',
            body: requestBodyStr,
            headers: {
                'Authorization': `Basic ${Buffer.from(`${username}:${password}`, "utf-8").toString("base64")}`,
                'Content-Type': 'application/json',
            },
        });

        console.log(`Response status: ${response.status} ${response.statusText}`);
        if (response.ok) {
            process.exit(0);
        }
        else {
            const responseBody = await response.json();
            console.log('Response:', responseBody);
            process.exit(1);
        }
    }
    catch (error) {
        console.log('Error:', error);
        process.exit(1);
    }
}

sendRequest();
