<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $casts = [
        'bill_date' => 'date',
        'letterhead_show_watermark' => 'boolean',
        'letterhead_show_border' => 'boolean',
        'letterhead_watermark_opacity' => 'integer',
        'letterhead_chs_last_no' => 'integer',
        'letterhead_ps_last_no' => 'integer',
        'letterhead_name_en_size' => 'float',
        'letterhead_name_np_size' => 'float',
        'letterhead_address_size' => 'float',
        'letterhead_name_letter_spacing' => 'float',
        'letterhead_name_line_height' => 'float',
    ];

    protected $fillable = [
        'name',
        'client',
        'project',
        'contract_no',
        'bill_date',
        'address',
        'email',
        'phone',
        'website',
        'tax_number',
        'city',
        'state',
        'country',
        'zip',
        'logo',
        'favicon',
        'letterhead_template',
        'letterhead_primary_color',
        'letterhead_font_family',
        'letterhead_header_alignment',
        'letterhead_tagline',
        'letterhead_name_en_size',
        'letterhead_name_np_size',
        'letterhead_address_size',
        'letterhead_name_letter_spacing',
        'letterhead_name_line_height',
        'letterhead_name_en_color',
        'letterhead_name_np_color',
        'letterhead_address_color',
        'letterhead_name_font_style',
        'letterhead_name_en_align',
        'letterhead_name_np_align',
        'letterhead_address_align',
        'letterhead_meta_chs_align',
        'letterhead_meta_ps_align',
        'letterhead_meta_date_align',
        'letterhead_meta_chs_value',
        'letterhead_meta_ps_value',
        'letterhead_meta_date_value',
        'letterhead_layout_json',
        'letterhead_footer_text',
        'letterhead_watermark_text',
        'letterhead_watermark_mode',
        'letterhead_show_watermark',
        'letterhead_watermark_opacity',
        'letterhead_show_border',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function letterheadAssets()
    {
        return $this->hasMany(CompanyLetterheadAsset::class)->orderBy('sort_order')->orderBy('id');
    }

    public function letterheadExports()
    {
        return $this->hasMany(CompanyLetterheadExport::class)->latest();
    }

    /**
     * Get logo URL
     */
    public function getLogoUrl(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        
        try {
        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        
        if ($storage->exists($this->logo)) {
            // Use asset() for better compatibility with different server configurations
            return asset('storage/' . $this->logo);
        }
        
            // File doesn't exist - clear it from database (silently fail if update fails)
            try {
        $this->update(['logo' => null]);
            } catch (\Exception $e) {
                \Log::warning('Failed to clear logo from database: ' . $e->getMessage());
            }
            return null;
        } catch (\Exception $e) {
            \Log::error('Error getting logo URL: ' . $e->getMessage());
        return null;
        }
    }

    /**
     * Get favicon URL, generating default if needed
     */
    public function getFaviconUrl(): string
    {
        try {
        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        
        // Check if favicon exists in database and on disk
        if ($this->favicon) {
            if ($storage->exists($this->favicon)) {
                // Use asset() for better compatibility with different server configurations
                return asset('storage/' . $this->favicon);
            } else {
                    // File path exists in DB but file doesn't exist - clear it (silently fail if update fails)
                    try {
                $this->update(['favicon' => null]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to clear favicon from database: ' . $e->getMessage());
                    }
            }
        }
        
        // Generate default if not exists
        if ($this->name) {
            try {
                $faviconService = app(\App\Services\FaviconGeneratorService::class);
                $faviconPath = $faviconService->generateDefaultFavicon($this->name);
                
                // Verify file was created
                if ($storage->exists($faviconPath)) {
                        try {
                    $this->update(['favicon' => $faviconPath]);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to save favicon to database: ' . $e->getMessage());
                        }
                    return asset('storage/' . $faviconPath);
                }
            } catch (\Exception $e) {
                // Log error for debugging
                \Log::error('Favicon generation failed: ' . $e->getMessage());
            }
            }
        } catch (\Exception $e) {
            \Log::error('Error getting favicon URL: ' . $e->getMessage());
        }
        
        // Fallback
        return asset('favicon.ico');
    }
}


