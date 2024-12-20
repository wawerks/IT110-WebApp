const express = require('express');
const { Client } = require('pg'); // PostgreSQL client
const router = express.Router();

// PostgreSQL client setup
const client = new Client({
  user: 'your_username',
  host: 'localhost',
  database: 'web-app2',
  password: '123',
  port: 5432,
});

client.connect();

// GET route to fetch lost items
router.get('/lost-items', (req, res) => {
  const query = 'SELECT * FROM lost_items'; // Replace with your actual query

  client.query(query, (error, result) => {
    if (error) {
      console.error('Error fetching lost items:', error);
      return res.status(500).json({ error: 'Error fetching lost items' });
    }

    return res.status(200).json(result.rows); // Send the fetched rows back as JSON
  });
});

module.exports = router;
