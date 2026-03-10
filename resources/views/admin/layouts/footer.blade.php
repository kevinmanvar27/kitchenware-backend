<!-- Footer -->
<footer class="bg-surface border-top border-default py-4 mt-auto">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="d-flex align-items-center">
                    @if(setting('footer_logo'))
                        <img src="{{ asset('storage/' . setting('footer_logo')) }}" alt="{{ setting('site_title', 'Admin Panel') }}" class="me-3 rounded" height="30">
                    @endif
                    <span class="text-secondary small">
                        {{ setting('footer_text', '© ' . date('Y') . ' ' . config('app.name', 'Laravel') . '. All rights reserved.') }}
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <ul class="nav justify-content-md-end">
                    @if(setting('facebook_url'))
                    <li class="nav-item">
                        <a class="nav-link text-secondary px-2 py-0" href="{{ setting('facebook_url') }}" target="_blank" data-bs-toggle="tooltip" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </li>
                    @endif
                    @if(setting('twitter_url'))
                    <li class="nav-item">
                        <a class="nav-link text-secondary px-2 py-0" href="{{ setting('twitter_url') }}" target="_blank" data-bs-toggle="tooltip" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </li>
                    @endif
                    @if(setting('instagram_url'))
                    <li class="nav-item">
                        <a class="nav-link text-secondary px-2 py-0" href="{{ setting('instagram_url') }}" target="_blank" data-bs-toggle="tooltip" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </li>
                    @endif
                    @if(setting('linkedin_url'))
                    <li class="nav-item">
                        <a class="nav-link text-secondary px-2 py-0" href="{{ setting('linkedin_url') }}" target="_blank" data-bs-toggle="tooltip" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </li>
                    @endif
                    @if(setting('youtube_url'))
                    <li class="nav-item">
                        <a class="nav-link text-secondary px-2 py-0" href="{{ setting('youtube_url') }}" target="_blank" data-bs-toggle="tooltip" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </li>
                    @endif
                    @if(setting('whatsapp_url'))
                    <li class="nav-item">
                        <a class="nav-link text-secondary px-2 py-0" href="{{ setting('whatsapp_url') }}" target="_blank" data-bs-toggle="tooltip" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</footer>