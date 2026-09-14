import {
    Activity,
    Award,
    ClipboardList,
    Code2,
    FolderKanban,
    Globe,
    Headphones,
    Headset,
    Lightbulb,
    PenTool,
    Rocket,
    Search,
    Shield,
    ShoppingBag,
    Sparkles,
    Workflow,
} from 'lucide-react';

// Importa explícitamente los iconos usados por el CMS (ver `icon` en los
// seeders de home/servicios) en vez de `import * as icons` para que Vite
// pueda seguir aplicando tree-shaking; el ícono es texto libre en el admin,
// así que uno no listado aquí cae al ícono por defecto.
const ICONS = {
    Activity,
    Award,
    ClipboardList,
    Code2,
    FolderKanban,
    Globe,
    Headphones,
    Headset,
    Lightbulb,
    PenTool,
    Rocket,
    Search,
    Shield,
    ShoppingBag,
    Workflow,
};

export default function Icon({ name, className = 'size-5' }) {
    const Component = (name && ICONS[name]) || Sparkles;

    return <Component className={className} aria-hidden="true" />;
}
