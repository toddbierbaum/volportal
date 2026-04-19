@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ rtrim(config('app.url'), '/') }}/images/logo-dark.png?v={{ config('app.version') }}"
     class="logo"
     width="140"
     height="71"
     style="height:56px;width:auto;max-height:56px;"
     alt="Florida Chautauqua Theater &amp; Institute">
</a>
</td>
</tr>
