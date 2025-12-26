# Email Validation API Setup Guide

## ✅ Implementation Complete

Email validation अब दुई step मा काम गर्छ:

1. **Domain Check (FREE)** - Already working
   - Domain मा mail server छ कि छैन check गर्छ
   - Example: `gmail.com` मा mail server छ

2. **Email Address Verification (FREE API)** - New!
   - Actual email address exist गर्छ कि छैन check गर्छ
   - Example: `test@gmail.com` exist गर्छ कि छैन verify गर्छ

## 🔑 API Key Setup (Required)

### Step 1: Get Free API Key

1. Go to: https://www.abstractapi.com/email-validation-api
2. Click "Get Started" or "Sign Up"
3. Create free account
4. Get your API key (100 free validations/month)

### Step 2: Add API Key to .env

`.env` file मा यो line add गर्नुहोस्:

```env
ABSTRACTAPI_EMAIL_KEY=your_api_key_here
```

### Step 3: Clear Config Cache

```bash
php artisan config:clear
```

## 📊 How It Works

### With API Key:
1. Domain check (FREE) ✅
2. Email address verification (API) ✅
3. `test@gmail.com` exist गर्छ कि छैन check गर्छ

### Without API Key:
1. Domain check (FREE) ✅
2. Email address verification skip हुन्छ
3. Domain valid भए pass हुन्छ (email exist check हुँदैन)

## 💰 Cost

- **Free**: 100 email validations/month
- **After free tier**: $9/month for 1,000 validations
- **Domain check**: Always FREE (unlimited)

## 🎯 Features

- ✅ Domain validation (FREE)
- ✅ Email address existence check
- ✅ Gmail account verification
- ✅ SMTP verification
- ✅ Disposable email detection
- ✅ Automatic fallback if API fails

## ⚠️ Important Notes

1. **API Key Required**: Without API key, only domain check works
2. **Rate Limits**: 100 free/month, then paid
3. **Fallback**: If API fails, domain check still works
4. **Timeout**: API call timeout is 5 seconds

## 🧪 Testing

After adding API key, test with:
- Valid email: `test@gmail.com` (if exists) → ✅ Pass
- Invalid email: `random123@gmail.com` (doesn't exist) → ❌ Block
- Invalid domain: `test@invalid-xyz.com` → ❌ Block

---

**Ready to use! Just add your API key to .env file.**

