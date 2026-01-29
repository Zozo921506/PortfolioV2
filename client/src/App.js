import React, { useState, useEffect } from 'react';
import './App.css';
import CustomCursor from './CustomCursor';
import Profile from './Profile';
import Contact from './Contact';
import Project from './Project';

function App() {
  const [screenTitle, setScreenTitle] = useState(true);
  const [profile, setProfile] = useState(false);
  const [projects, setProjects] = useState(false);
  const [contact, setContact] = useState(false);
  const [showTransition, setShowTransition] = useState(false);
  const [clicks, setClicks] = useState([]);

  const handlePageChange = (newPage) => {
    setShowTransition(true);
    setTimeout(() => {
      setScreenTitle(newPage === "home");
      setProfile(newPage === "profile");
      setProjects(newPage === "projects");
      setContact(newPage === "contact");
      setShowTransition(false);
    }, 600);
  };

  const handleClick = (e) => {
    const newClick = {
      id: Date.now(),
      x: e.clientX,
      y: e.clientY,
    };
    setClicks((prev) => [...prev, newClick]);
    setTimeout(() => {
      setClicks((prev) => prev.filter((click) => click.id !== newClick.id));
    }, 400);
  };

  useEffect(() => {
    if (!screenTitle && !profile && !projects && !contact)
    {
      setScreenTitle(true);
    }

  }, [screenTitle, profile, projects, contact]);
  return (
    <div onClick={handleClick}>
      {clicks.map((click) => (
        <div key={click.id} className="click-effect" style={{ top: click.y, left: click.x }}></div>
      ))}
      <CustomCursor />
      {screenTitle ? (
        <div id='mainscreen-container'>
          <nav>
            <img src='./Image/Falla_Enzo_pic.png' alt='Profil' id='mainscreen-image'></img>
            <p>Falla Enzo</p>
          </nav>
          <div className='mainscreen'>
            <div id='portfolio'>Portfolio</div>
            <div className='mainscreen_button'>
              <button onClick={(e) => handlePageChange('profile')}>Profil</button>
              <button onClick={(e) => handlePageChange('projects')}>Projets</button>
              <button onClick={(e) => handlePageChange('contact')}>Contact</button>
            </div>
          </div>
        </div>
      ) : (
        <>
          {profile ? (
            <>
              {showTransition && <div className="circle-transition"></div>}
              <Profile />
              <footer>
                <button onClick={(e) => handlePageChange('home')} id='accueil'>Accueil</button>
                <button onClick={(e) => handlePageChange('projects')}>Projets</button>
                <button onClick={(e) => handlePageChange('contact')}>Contact</button>
                <img src='./Image/Falla_Enzo_pic.png' alt='Profil' className='footer-image'></img>
                <p className="footer-name">Falla Enzo</p>
              </footer>
            </>
          ) : projects ? (
            <>
              {showTransition && <div className="circle-transition"></div>}
              <Project />
              <footer>
                <button onClick={(e) => handlePageChange('home')} id='accueil'>Accueil</button>
                <button onClick={(e) => handlePageChange('profile')}>Profil</button>
                <button onClick={(e) => handlePageChange('contact')}>Contact</button>
                <img src='./Image/Falla_Enzo_pic.png' alt='Profil' className='footer-image'></img>
                <p className="footer-name">Falla Enzo</p>
              </footer>
            </>
          ) : contact ? (
            <>
              {showTransition && <div className="circle-transition"></div>}
              <Contact />
              <footer>
                <button onClick={(e) => handlePageChange('home')} id='accueil'>Accueil</button>
                <button onClick={(e) => handlePageChange('profile')}>Profil</button>
                <button onClick={(e) => handlePageChange('projects')}>Projets</button>
                <img src='./Image/Falla_Enzo_pic.png' alt='Profil' className='footer-image'></img>
                <p className="footer-name">Falla Enzo</p>
              </footer>
            </>
          ) : (
            <>
              <p>Loading ...</p>
            </>
          )}
        </>
      )}
    </div>
  );
}

export default App;
