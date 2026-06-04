// File: /api/chat.js
// Place this file at the root of your Vercel project inside an /api folder

export default async function handler(req, res) {
  // Allow requests from your own site
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  // Handle preflight
  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'POST') return res.status(405).json({ error: 'Method not allowed' });

  try {
    const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${process.env.OPENROUTER_API_KEY}`, // set this in Vercel dashboard
        'HTTP-Referer': 'https://soac.club',
        'X-Title': 'SOAC Cosmos Widget',
      },
      body: JSON.stringify(req.body),
    });

    const data = await response.json();
    res.status(response.status).json(data);
  } catch (err) {
    console.error('[/api/chat]', err);
    res.status(500).json({ error: { message: 'Internal server error' } });
  }
}
