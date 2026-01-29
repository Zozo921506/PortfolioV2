import React, { useState, useEffect } from 'react';
import './CustomCursor.css';

const CustomCursor = () => {
  const [position, setPosition] = useState({ x: 0, y: 0 });

  useEffect(() => {
    const updatePosition = (e) => {
      setPosition({ x: e.clientX, y: e.clientY });
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
          transform: `translate(${position.x - 10}px, ${position.y - 10}px)`,
        }}
      ></div>
      <div
        id="cursor-trail"
        style={{
          transform: `translate(${position.x - 20}px, ${position.y - 20}px)`,
        }}
      ></div>
    </>
  );
};

export default CustomCursor;
