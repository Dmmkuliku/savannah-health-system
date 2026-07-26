/**
 * Vercel serverless entry — proxies every request to the Render Laravel backend.
 * Env: RENDER_BACKEND_URL=https://your-service.onrender.com
 */
module.exports = async function handler(req, res) {
  const backend = (process.env.RENDER_BACKEND_URL || process.env.BACKEND_URL || '').replace(/\/$/, '');

  if (!backend) {
    res.statusCode = 503;
    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    res.end(`<!DOCTYPE html><html><body style="font-family:system-ui;padding:2rem;background:#f3fbf0;color:#28461f">
      <h1>Savannah Health System</h1>
      <p>Backend URL not configured. Set <code>RENDER_BACKEND_URL</code> in Vercel to your Render service URL.</p>
    </body></html>`);
    return;
  }

  const host = req.headers['x-forwarded-host'] || req.headers.host || 'localhost';
  const incomingUrl = new URL(req.url, `https://${host}`);
  const target = `${backend}${incomingUrl.pathname}${incomingUrl.search}`;

  const headers = { ...req.headers };
  delete headers.host;
  delete headers['content-length'];
  headers['x-forwarded-host'] = host;
  headers['x-forwarded-proto'] = 'https';
  headers['x-savannah-via'] = 'vercel';

  const method = req.method || 'GET';
  const init = { method, headers, redirect: 'manual' };

  if (method !== 'GET' && method !== 'HEAD') {
    const chunks = [];
    for await (const chunk of req) chunks.push(chunk);
    init.body = Buffer.concat(chunks);
  }

  let upstream;
  try {
    upstream = await fetch(target, init);
  } catch (err) {
    res.statusCode = 502;
    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    res.end(`<!DOCTYPE html><html><body style="font-family:system-ui;padding:2rem;background:#f3fbf0;color:#28461f">
      <h1>Savannah Health System</h1>
      <p>Waking hospital backend on Render (free tier sleep)… Refresh in about 1 minute.</p>
      <p style="font-size:12px;opacity:.7">${String(err && err.message ? err.message : err)}</p>
    </body></html>`);
    return;
  }

  res.statusCode = upstream.status;
  const setCookies = [];
  upstream.headers.forEach((value, key) => {
    const k = key.toLowerCase();
    if (['content-encoding', 'transfer-encoding', 'content-length'].includes(k)) return;
    if (k === 'set-cookie') {
      setCookies.push(
        value.replace(/;\s*Domain=[^;]+/gi, '').replace(/;\s*SameSite=[^;]+/gi, '; SameSite=None')
      );
      return;
    }
    res.setHeader(key, value);
  });
  if (setCookies.length) {
    res.setHeader('set-cookie', setCookies);
  }

  const buf = Buffer.from(await upstream.arrayBuffer());
  res.end(buf);
};
