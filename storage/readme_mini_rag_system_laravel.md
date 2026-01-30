# Mini RAG System with WebSocket Streaming (Laravel)

## 📌 Overview
هذا المشروع عبارة عن **Mini Retrieval-Augmented Generation (RAG) System** مبني باستخدام **Laravel**، يتيح للمستخدمين:
- تسجيل الدخول بشكل آمن
- رفع ملفات PDF
- فهرسة محتوى الملفات باستخدام Embeddings
- الدردشة مع LLM للإجابة على الأسئلة اعتمادًا على محتوى الملفات
- استلام الردود بشكل **Real-time Streaming عبر WebSocket**

المشروع مصمم ليتبع **Best Practices** في Laravel مع التركيز على:
- Clean Code
- SOLID Principles
- Security
- Extensibility

---

## 🧠 System Architecture

### High Level Flow
1. User Authentication (Sanctum)
2. PDF Upload & Validation
3. Text Extraction & Chunking
4. Embedding Generation
5. Vector Storage (Qdrant)
6. WebSocket Chat
7. Context Retrieval (RAG)
8. LLM Streaming Response

### Architecture Pattern
- Controller Layer
- Service Layer
- Infrastructure Layer

```
Controller
   ↓
Service (Business Logic)
   ↓
Infrastructure (LLM / Vector DB / PDF)
```

---

## 🔐 Authentication

- Laravel Sanctum (Token-based)
- جميع REST APIs و WebSocket محمية
- أي محاولة اتصال WebSocket بدون Token:
  - يتم رفضها فورًا
  - يتم تسجيلها في Logs

### Login Endpoint
```
POST /api/v1/login
```
Response:
```json
{
  "token": "SANCTUM_TOKEN"
}
```

---

## 📄 PDF Upload & Indexing

### Endpoint
```
POST /api/v1/pdf/upload
Authorization: Bearer TOKEN
```

### Validation Rules
- PDF files only
- Max size configurable
- Reject empty or corrupted files

### Processing Steps
1. Store PDF securely
2. Extract text from PDF
3. Clean & normalize text
4. Chunk text (fixed size + overlap)
5. Generate embeddings
6. Store embeddings in Vector DB
7. Link data to authenticated user

---

## 🧠 Vector Database

- Qdrant (REST-based)
- Each embedding scoped by `user_id`
- Supports similarity search

Stored Metadata:
- user_id
- pdf_id
- chunk_index

---

## 🔌 WebSocket Chat (RAG)

### Connection Rules
- Authenticated users only
- Token validated on connection
- Unauthorized attempts:
  - Disconnected immediately
  - Logged

### Chat Flow
1. User sends query
2. Generate query embedding
3. Retrieve top-K relevant chunks
4. Build context
5. Send prompt to LLM
6. Stream response chunk-by-chunk

### Example Message
```json
{ "query": "What is this document about?" }
```

---

## 🤖 LLM Integration

- OpenAI API
- Streaming enabled
- Prompt structure:

```
System: Answer using the provided context only
Context: ...
Question: ...
```

Streaming handled via WebSocket events.

---

## 🧩 Project Structure

```
app/
 ├── Http/Controllers/
 ├── Services/
 │   ├── Pdf/
 │   ├── AI/
 │   └── Vector/
 ├── WebSockets/
 └── Models/
```

---

## ⚠️ Error Handling

Standard API Response Format:
```json
{
  "success": false,
  "message": "Error description",
  "errors": []
}
```

Handled Edge Cases:
- Invalid / empty PDF
- Empty query
- Unauthorized access
- No relevant context found

---

## 🧪 Logging

- Unauthorized WebSocket attempts
- PDF processing failures
- LLM errors

Used for debugging and monitoring.

---

## ⚙️ Environment Variables

```
OPENAI_API_KEY=
QDRANT_HOST=
QDRANT_PORT=
SANCTUM_STATEFUL_DOMAINS=
```

---

## 🚀 Local Setup

1. Clone repository
2. Install dependencies
```
composer install
```
3. Setup `.env`
4. Run migrations
```
php artisan migrate
```
5. Start WebSocket server
```
php artisan websockets:serve
```
6. Serve application
```
php artisan serve
```

---

## ✅ Design Principles Applied

- SOLID Principles
- Service Pattern
- Strategy Pattern (LLM / Vector DB)
- Dependency Injection
- Separation of Concerns

---

## 📈 Evaluation Alignment

This implementation focuses on:
- Clean & readable code
- Secure authentication
- Correct RAG pipeline
- Real-time WebSocket streaming
- Clear documentation

---

## 🧠 Sample Flow

1. User logs in → receives token
2. Uploads PDF
3. System indexes content
4. User opens WebSocket
5. Sends query
6. Receives streamed AI response

---

## 🏁 Conclusion

This project demonstrates a real-world backend system using Laravel with AI integration, designed to be scalable, secure, and maintainable.

