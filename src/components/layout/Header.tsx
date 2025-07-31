import { APP_CONFIG } from '../../../config/app.config';

export function Header() {
  return (
    <header className="bg-[#1a679f] text-white">
      <div className="container mx-auto px-4 py-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center">
            <img
              src={APP_CONFIG.branding.LOGO_URL}
              alt="GlowHost"
              className="h-8 w-auto object-contain sm:h-10 md:h-12"
            />
          </div>
          <div className="text-right text-sm">
            <div className="text-cyan-200">{APP_CONFIG.branding.SUPPORT_INFO}</div>
            <div className="font-semibold">
              Toll Free Sales{' '}
              <a
                href={APP_CONFIG.branding.PHONE_LINK}
                className="hover:text-cyan-200"
              >
                {APP_CONFIG.branding.PHONE_DISPLAY}
              </a>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}
