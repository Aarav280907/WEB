-- Practical 12 sample posters: attaches the generated demo posters (Practical-12/uploads) to four seeded events.
USE studenthub;
UPDATE events SET poster='849267c34b2312b0ca3433c6.jpg' WHERE id=1;
UPDATE events SET poster='fcaab2971793d7a1ee42bfb0.jpg' WHERE id=4;
UPDATE events SET poster='f388e4787b4444de2cbf8636.jpg' WHERE id=6;
UPDATE events SET poster='9f034d34ab25b8d7cfef8045.jpg' WHERE id=15;
