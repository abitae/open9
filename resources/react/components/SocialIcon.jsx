import { Facebook, Globe, Instagram, Linkedin, Twitter, Youtube } from 'lucide-react';

const PLATFORM_ICONS = {
    twitter: Twitter,
    linkedin: Linkedin,
    instagram: Instagram,
    facebook: Facebook,
    youtube: Youtube,
};

export default function SocialIcon({ platform, className = 'size-5' }) {
    const Component = PLATFORM_ICONS[platform] ?? Globe;

    return <Component className={className} aria-hidden="true" />;
}
