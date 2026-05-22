<?php

return [
    'version' => '1.0.0',
    'name' => 'SmartLead CRM Pro',
    'lead_statuses' => [
        'new' => 'New', 'contacted' => 'Contacted', 'interested' => 'Interested',
        'follow_up' => 'Follow-Up', 'proposal_sent' => 'Proposal Sent',
        'negotiation' => 'Negotiation', 'won' => 'Won', 'lost' => 'Lost',
        'not_interested' => 'Not Interested', 'duplicate' => 'Duplicate',
    ],
    'lead_sources' => [
        'website' => 'Website', 'referral' => 'Referral', 'google_ads' => 'Google Ads',
        'facebook_ads' => 'Facebook Ads', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn',
        'cold_call' => 'Cold Call', 'email_campaign' => 'Email Campaign',
        'trade_show' => 'Trade Show', 'partner' => 'Partner', 'direct' => 'Direct', 'other' => 'Other',
    ],
    'lead_priorities' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'],
    'followup_types' => [
        'call' => 'Call', 'email' => 'Email', 'whatsapp' => 'WhatsApp', 'meeting' => 'Meeting',
        'site_visit' => 'Site Visit', 'demo' => 'Demo', 'video_call' => 'Video Call', 'custom' => 'Custom',
    ],
    'followup_statuses' => [
        'pending' => 'Pending', 'completed' => 'Completed', 'missed' => 'Missed',
        'cancelled' => 'Cancelled', 'rescheduled' => 'Rescheduled',
    ],
    'quotation_statuses' => [
        'draft' => 'Draft', 'sent' => 'Sent', 'viewed' => 'Viewed', 'accepted' => 'Accepted',
        'rejected' => 'Rejected', 'expired' => 'Expired', 'converted' => 'Converted',
    ],
    'pipeline_stages' => [
        'new_lead' => 'New Lead', 'qualified' => 'Qualified', 'contacted' => 'Contacted',
        'proposal' => 'Proposal', 'negotiation' => 'Negotiation', 'won' => 'Won', 'lost' => 'Lost',
    ],
    'task_priorities' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'],
    'task_statuses' => ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'],
    'currency' => env('APP_CURRENCY', 'INR'),
    'currency_symbol' => env('APP_CURRENCY_SYMBOL', '₹'),
    'date_format' => env('APP_DATE_FORMAT', 'd-m-Y'),
    'time_format' => env('APP_TIME_FORMAT', 'h:i A'),
    'quotation_prefix' => 'QT',
    'invoice_prefix' => 'INV',
    'pagination' => ['default' => 25, 'options' => [10, 25, 50, 100]],
    'file_upload' => ['max_size' => 10240, 'allowed_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'csv']],
    'white_label' => [
        'enabled' => env('WHITE_LABEL_ENABLED', false),
        'name' => env('WHITE_LABEL_NAME', 'SmartLead CRM Pro'),
        'logo' => env('WHITE_LABEL_LOGO'),
    ],
];
