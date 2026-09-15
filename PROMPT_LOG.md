Video script — whole project :

        "I've built the Store Order & Inventory Laravel API — orders, stock deduction, low-stock endpoint, order history, and a queued confirmation job. I need to record a 6-8 minute screen recording walking through it for a take-home submission. Can you give me a section-by-section script covering: intro, schema, order creation demo (including the insufficient-stock case), order history, low-stock endpoint, the queued job, how I handled concurrent orders on the last unit of stock.

Queues and jobs :

        The brief says I need to dispatch a queued job when an order is created, to simulate sending a confirmation email, with no real SMTP needed. Walk me through how to set this up as a proper Job class in Laravel 11 — what goes in the job, how dispatching from a service works.


README document

        Write me a README for this Laravel take-home task. It needs setup instructions, an explanation of the schema, a list of the API endpoints with example requests/responses, and a section documenting the assumptions I made anywhere the brief was ambiguous — like whether customer_name is always required, how I handled the tax calculation, and why I didn't build a UI.

How the last unit of stock is handled :

        Two customers try to order the same product at almost the same time and there's only 1 unit left in stock. How do I make sure only one order succeeds and the other fails cleanly with no overselling? Can I use DB transactions for this feature? why I can't write a real PHPUnit test for this and what to do instead to prove it works.