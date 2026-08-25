import React, { useState, useEffect } from 'react';
import './CustomCursor.css';

const CustomCursor = () => {
    const [position, setPosition] = useState({ x: 0, y: 0 });

    useEffect(() => {
        const updatePosition = (e) => {
            setPosition({
                x: e.clientX,
                y: e.clientY
            });
        };

        window.addEventListener('mousemove', updatePosition);

        return () => {
            window.removeEventListener('mousemove', updatePosition);
        };
    }, []);

    return (
        <>
            <div
                id="custom-cursor"
                style={{
                    left: `${position.x}px`,
                    top: `${position.y}px`,
                }}
            />

            <div
                id="cursor-trail"
                style={{
                    left: `${position.x}px`,
                    top: `${position.y}px`,
                }}
            />
        </>
    );
};

export default CustomCursor;
