{{-- Partial: include in every newsletter email --}}
{{-- Expects $email variable with the subscriber's email address --}}

<tr>
    <td style="padding: 24px 0; border-top: 1px solid #e2e8f0;">
        <p style="margin: 0 0 8px; font-size: 14px; line-height: 1.5; color: #64748b; text-align: center;">
            {{ __('messages.newsletter.email_footer_line1') }}
        </p>
        <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #94a3b8; text-align: center;">
            <a href="{{ route('newsletter.unsubscribe', ['email' => base64_encode($email)]) }}" style="color: #00346f; text-decoration: underline;">
                {{ __('messages.newsletter.email_unsubscribe_link') }}
            </a>
            &nbsp;·&nbsp;
            <a href="{{ route('pages.show', ['slug' => 'privacy-policy']) }}" style="color: #64748b; text-decoration: underline;">
                {{ __('messages.footer.privacy') }}
            </a>
        </p>
    </td>
</tr>
