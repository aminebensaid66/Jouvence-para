# Product-page delivery and WhatsApp context — JP-PDP-002

The product page shows only approved JP-DEC-004 delivery facts: nationwide 7,000 TND delivery, free from 200,000 TND after discounts, two working days excluding weekends/holidays, free store pickup, First Delivery/manual processing. A one-time generated `livraison` WordPress page publishes the same approved facts and is linked from the PDP.

WhatsApp click instrumentation emits `jouvencepara:analytics` only when analytics consent exists. Payload: event name, `context=product`, numeric product ID. It never contains message text, phone number, cookie values, email, customer name or URL query text.
