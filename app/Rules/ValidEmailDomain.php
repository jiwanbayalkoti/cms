<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ValidEmailDomain implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Extract domain from email
        $domain = substr(strrchr($value, "@"), 1);
        
        if (empty($domain)) {
            return false;
        }
        
        // Step 1: Domain check (FREE - always done)
        // Primary check: Domain must have MX (Mail Exchange) records
        if (!checkdnsrr($domain, 'MX')) {
            return false; // Domain doesn't have mail servers
        }
        
        // Step 2: API verification (FREE - 100/month)
        // Check if actual email address exists using AbstractAPI
        $apiKey = config('services.abstractapi.key');
        
        if (!empty($apiKey)) {
            try {
                // Use cURL similar to AbstractAPI example
                $ch = curl_init();
                
                // Build URL with API key and email (using emailreputation API)
                $url = 'https://emailreputation.abstractapi.com/v1/?api_key=' . urlencode($apiKey) . '&email=' . urlencode($value);
                
                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                
                // Execute the request
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                
                // Close the cURL handle
                curl_close($ch);
                
                if ($curlError) {
                    Log::warning('Email validation cURL error: ' . $curlError);
                    // Continue to fallback
                } else                    if ($httpCode === 200 && $response) {
                    // Track API usage only when API call is successful
                    $this->incrementApiUsageCount();
                    
                    $data = json_decode($response, true);
                    
                    if ($data && !isset($data['error'])) {
                        // Email Reputation API response structure
                        $deliverability = $data['email_deliverability'] ?? null;
                        
                        if ($deliverability) {
                            $status = $deliverability['status'] ?? null; // "deliverable" or "undeliverable"
                            $isSmtpValid = $deliverability['is_smtp_valid'] ?? false;
                            $isFormatValid = $deliverability['is_format_valid'] ?? false;
                            $statusDetail = $deliverability['status_detail'] ?? null;
                            
                            // Email is valid if:
                            // 1. Status is "deliverable"
                            // 2. SMTP is valid (email exists)
                            // 3. Format is valid
                            
                            if ($status === 'deliverable' && $isSmtpValid && $isFormatValid) {
                                return true; // Email exists and is valid
                            }
                            
                            // If status is "undeliverable" or SMTP is invalid, email doesn't exist
                            if ($status === 'undeliverable' || !$isSmtpValid) {
                                return false; // Email doesn't exist
                            }
                            
                            // If format is invalid, reject
                            if (!$isFormatValid) {
                                return false;
                            }
                            
                            // Default to invalid if we can't confirm
                            return false;
                        }
                        
                        // If deliverability data not found, reject
                        return false;
                    } else {
                        // API returned error
                        Log::warning('Email validation API error', [
                            'http_code' => $httpCode,
                            'response' => $response,
                            'email' => $value
                        ]);
                    }
                } else {
                    // API call failed (401, 500, etc.)
                    if ($httpCode === 401) {
                        // Invalid API key - log error
                        Log::error('Email validation API key is INVALID (401) - Please check API key in .env file', [
                            'http_code' => $httpCode,
                            'response' => $response,
                            'email' => $value
                        ]);
                        // Reject email if API key is invalid - user must fix API key
                        return false;
                    } else {
                        // Other API errors - log but allow domain check as fallback
                        Log::warning('Email validation API failed', [
                            'http_code' => $httpCode,
                            'response' => $response,
                            'email' => $value
                        ]);
                        // Continue to fallback (domain check only) for other errors
                    }
                }
            } catch (\Exception $e) {
                // If API fails (timeout, network error), fallback to domain check only
                // Log error but don't block validation
                Log::warning('Email validation API exception: ' . $e->getMessage());
            }
        }
        
        // Fallback: If no API key or API failed, use domain check only
        // Domain has MX records, so it's valid (but we can't verify if email exists)
        // This is acceptable for basic validation
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute does not exist or is not a valid email address. Please enter a valid email address.';
    }
    
    /**
     * Increment API usage count for tracking
     */
    private function incrementApiUsageCount()
    {
        $monthKey = 'email_validation_api_count_' . now()->format('Y-m');
        $count = Cache::get($monthKey, 0);
        // Store until end of current month
        $expiresAt = now()->endOfMonth();
        Cache::put($monthKey, $count + 1, $expiresAt);
    }
    
    /**
     * Get current month's API usage count
     */
    public static function getApiUsageCount(): int
    {
        $monthKey = 'email_validation_api_count_' . now()->format('Y-m');
        return Cache::get($monthKey, 0);
    }
    
    /**
     * Get API usage limit (free tier)
     */
    public static function getApiUsageLimit(): int
    {
        return 100; // Free tier limit per month
    }
}

