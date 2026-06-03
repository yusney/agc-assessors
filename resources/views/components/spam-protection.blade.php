{{--
    Spam protection fields — include inside every POST form before @csrf.
    - _h_url / _h_name: honeypot fields (hidden via CSS, bots fill them)
    - _form_token: encrypted timestamp, validated server-side (min 3s elapsed)
--}}
<div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden" tabindex="-1">
    <label for="_h_url">Website</label>
    <input type="text" id="_h_url" name="_h_url" value="" autocomplete="off" tabindex="-1">
    <label for="_h_name">Full name</label>
    <input type="text" id="_h_name" name="_h_name" value="" autocomplete="off" tabindex="-1">
</div>
<input type="hidden" name="_form_token" value="{{ encrypt(now()->timestamp) }}">
