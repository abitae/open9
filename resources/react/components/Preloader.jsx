import RocketIcon from './RocketIcon';

export default function Preloader({ visible }) {
    return (
        <div className={`preloader ${visible ? '' : 'preloader-hidden'}`} aria-hidden={!visible}>
            <div className="preloader-rocket-track">
                <RocketIcon className="preloader-rocket" />
            </div>
            <p className="preloader-label">OPEN9</p>
        </div>
    );
}
