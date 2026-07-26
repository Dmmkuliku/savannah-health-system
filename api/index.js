/**
 * Vercel serverless proxy → Render Laravel backend.
 * Env: RENDER_BACKEND_URL=https://savannah-health-system.onrender.com
 */
module.exports = async (req, res) => {
  try {
    const backend = String(process.env.RENDER_BACKEND_URL || process.env.BACKEND_URL || '')
      .trim()
      .replace(/\/$/, '');

    if (!backend) {
      res.statusCode = 503;
      res.setHeader('Content-Type', 'text/html; charset=utf-8');
      res.end('<h1>Savannah Health System</h1><p>Set RENDER_BACKEND_URL in Vercel.</p>');
      return;
    }

    const host = req.headers['x-forwarded-host'] || req.headers.host || 'localhost';
    const url = new URL(req.url || '/', `https://${host}`);
    const target = `${backend}${url.pathname}${url.search}`;

    const headers = {};
    for (const [k, v] of Object.entries(req.headers || {})) {
      if (!v) continue;
      const key = k.toLowerCase();
      if (['host', 'connection', 'content-length'].includes(key)) continue;
      headers[k] = Array.isArray(v) ? v.join(',') : v;
    }
    headers['x-forwarded-host'] = host;
    headers['x-forwarded-proto'] = 'https';
    headers['x-savannah-via'] = 'vercel';

    const method = (req.method || 'GET').toUpperCase();
    const init = { method, headers, redirect: 'manual' };

    if (!['GET', 'HEAD'].includes(method)) {
      const chunks = [];
      await new Promise((resolve, reject) => {
        req.on('data', (c) => chunks.push(c));
        req.on('end', resolve);
        req.on('error', reject);
      });
      if (chunks.length) init.body = Buffer.concat(chunks);
    }

    const upstream = await fetch(target, init);
    res.statusCode = upstream.status;

    upstream.headers.forEach((value, key) => {
      const k = key.toLowerCase();
      if (['transfer-encoding', 'content-encoding', 'content-length'].includes(k)) return;
      if (k === 'set-cookie') {
        const cookies = typeof value === 'string' ? [value] : value;
        const cleaned = (Array.isArray(cookies) ? cookies : [cookies]).map((c) =>
          String(c).replace(/;\s*Domain=[^;]*/gi, '')
        );
        res.setHeader('set-cookie', cleaned);
        return;
      }
      try {
        res.setHeader(key, value);
      } catch (_) {}
    });

    const buf = Buffer.from(await upstream.arrayBuffer());
    res.end(buf);
  } catch (err) {
    res.statusCode = 502;
    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    res.end(
      `<!DOCTYPE html><html><body style="font-family:system-ui;padding:2rem;background:#f3fbf0;color:#28461f">
      <h1>Savannah Health System</h1>
      <p>Connecting to hospital backend on Render… Free tier may be waking up. Refresh in ~60s.</p>
      <pre style="font-size:12px;opacity:.7">${String(err && err.stack ? err.stack : err)}</pre>
      </body></html>`
    );
  }
};
