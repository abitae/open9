import AsteroidIcon from './AsteroidIcon';
import CometIcon from './CometIcon';
import PlanetIcon from './PlanetIcon';
import RocketIcon from './RocketIcon';
import SatelliteIcon from './SatelliteIcon';

const STARS = [
    { left: '4%', top: '8%', size: 1, delay: 0, duration: 3.4, layer: 'far' },
    { left: '11%', top: '22%', size: 1, delay: -0.8, duration: 4.1, layer: 'far' },
    { left: '18%', top: '6%', size: 2, delay: -1.4, duration: 2.8, layer: 'mid' },
    { left: '24%', top: '38%', size: 1, delay: -0.3, duration: 5.2, layer: 'far' },
    { left: '31%', top: '14%', size: 2, delay: -2.1, duration: 3.6, layer: 'mid' },
    { left: '37%', top: '48%', size: 1, delay: -1.1, duration: 4.8, layer: 'far' },
    { left: '42%', top: '9%', size: 3, delay: -0.6, duration: 2.4, layer: 'near' },
    { left: '49%', top: '27%', size: 1, delay: -1.8, duration: 3.9, layer: 'far' },
    { left: '55%', top: '12%', size: 2, delay: -2.6, duration: 3.1, layer: 'mid' },
    { left: '62%', top: '33%', size: 1, delay: -0.2, duration: 4.4, layer: 'far' },
    { left: '68%', top: '7%', size: 1, delay: -1.5, duration: 3.7, layer: 'far' },
    { left: '73%', top: '21%', size: 2, delay: -0.9, duration: 2.9, layer: 'mid' },
    { left: '79%', top: '44%', size: 1, delay: -2.2, duration: 5, layer: 'far' },
    { left: '86%', top: '16%', size: 3, delay: -1.3, duration: 2.6, layer: 'near' },
    { left: '92%', top: '31%', size: 1, delay: -0.4, duration: 4.2, layer: 'far' },
    { left: '8%', top: '58%', size: 2, delay: -2.8, duration: 3.3, layer: 'mid' },
    { left: '16%', top: '74%', size: 1, delay: -1.7, duration: 4.6, layer: 'far' },
    { left: '27%', top: '63%', size: 1, delay: -0.7, duration: 3.8, layer: 'far' },
    { left: '35%', top: '81%', size: 2, delay: -2.4, duration: 2.7, layer: 'mid' },
    { left: '44%', top: '69%', size: 1, delay: -1.9, duration: 5.1, layer: 'far' },
    { left: '52%', top: '86%', size: 2, delay: -0.5, duration: 3.5, layer: 'mid' },
    { left: '61%', top: '72%', size: 1, delay: -2.9, duration: 4.3, layer: 'far' },
    { left: '70%', top: '58%', size: 3, delay: -1.2, duration: 2.5, layer: 'near' },
    { left: '77%', top: '80%', size: 1, delay: -2, duration: 3.2, layer: 'far' },
    { left: '84%', top: '64%', size: 2, delay: -0.1, duration: 4, layer: 'mid' },
    { left: '91%', top: '78%', size: 1, delay: -1.6, duration: 4.7, layer: 'far' },
    { left: '13%', top: '42%', size: 1, delay: -2.7, duration: 3, layer: 'far' },
    { left: '58%', top: '51%', size: 2, delay: -0.8, duration: 2.8, layer: 'mid' },
];

export default function RocketField() {
    return (
        <div className="space-field" aria-hidden="true">
            {STARS.map((star, index) => (
                <span
                    key={`${star.left}-${star.top}`}
                    className={`space-star space-star-${star.layer}${index % 5 === 0 ? ' space-star-green' : ''}`}
                    style={{
                        left: star.left,
                        top: star.top,
                        width: star.size,
                        height: star.size,
                        '--twinkle': `${star.duration}s`,
                        animationDelay: `${star.delay}s, ${star.delay}s`,
                    }}
                />
            ))}

            <div className="space-cruise planet-track planet-track-a size-28 sm:size-36" style={{ animationDelay: '-22s' }}>
                <SatelliteIcon className="satellite-orbit size-8 sm:size-10" />
                <PlanetIcon variant="earth" className="relative z-[1] size-full" />
            </div>
            <div className="space-cruise planet-track planet-track-b size-24 sm:size-32" style={{ animationDelay: '-48s' }}>
                <PlanetIcon variant="ring" className="size-full" />
            </div>
            <div className="space-cruise planet-track planet-track-c size-14 sm:size-20" style={{ animationDelay: '-12s' }}>
                <PlanetIcon variant="gas" className="size-full" />
            </div>

            <div className="space-cruise satellite-free" style={{ animationDelay: '-9s' }}>
                <SatelliteIcon className="satellite-yaw size-10 sm:size-12" />
            </div>
            <div className="space-cruise satellite-free-b" style={{ animationDelay: '-4s' }}>
                <SatelliteIcon className="satellite-yaw size-8 sm:size-9" />
            </div>

            <div className="space-cruise asteroid-track asteroid-a" style={{ animationDelay: '-6s' }}>
                <AsteroidIcon className="asteroid-tumble size-8 sm:size-10" />
            </div>
            <div className="space-cruise asteroid-track asteroid-b" style={{ animationDelay: '-14s' }}>
                <AsteroidIcon className="asteroid-tumble size-6 sm:size-7" />
            </div>
            <div className="space-cruise asteroid-track asteroid-c" style={{ animationDelay: '-2s' }}>
                <AsteroidIcon className="asteroid-tumble size-5" />
            </div>

            <RocketIcon className="space-cruise rocket-cruise rocket-cruise-a size-16 sm:size-20" style={{ animationDelay: '-3s' }} />
            <RocketIcon className="space-cruise rocket-cruise rocket-cruise-b size-12 sm:size-14" style={{ animationDelay: '-11s' }} />
            <RocketIcon className="space-cruise rocket-cruise rocket-cruise-c size-10 sm:size-12" style={{ animationDelay: '-18s' }} />

            <CometIcon className="space-cruise comet-streak size-32 sm:size-44" style={{ animationDelay: '-6s' }} />
        </div>
    );
}
