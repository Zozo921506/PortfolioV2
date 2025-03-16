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

  const screenTittleButton = (e) => {
    e.preventDefault();
    setScreenTitle(true);
    setProfile(false);
    setProjects(false);
    setContact(false);
  }

  const profileButton = (e) => {
    e.preventDefault();
    setScreenTitle(false);
    setProfile(true);
    setProjects(false);
    setContact(false);
  }

  const projectsButton = (e) => {
    e.preventDefault();
    setScreenTitle(false);
    setProfile(false);
    setProjects(true);
    setContact(false);
  }

  const contactButton = (e) => {
    e.preventDefault();
    setScreenTitle(false);
    setProfile(false);
    setProjects(false);
    setContact(true);
  }

  useEffect(() => {
    if (!screenTitle && !profile && !projects && !contact)
    {
      setScreenTitle(true);
    }

  }, [screenTitle, profile, projects, contact]);
  return (
    <div>
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
              <button onClick={(e) => profileButton(e)}>Profil</button>
              <button onClick={(e) => projectsButton(e)}>Projets</button>
              <button onClick={(e) => contactButton(e)}>Contact</button>
            </div>
          </div>
        </div>
      ) : (
        <>
          {profile ? (
            <>
              <Profile />
              <footer>
                <button onClick={(e) => screenTittleButton(e)} id='accueil'>Accueil</button>
                <button onClick={(e) => projectsButton(e)}>Projets</button>
                <button onClick={(e) => contactButton(e)}>Contact</button>
                <img src='./Image/Falla_Enzo_pic.png' alt='Profil' className='footer-image'></img>
                <p className="footer-name">Falla Enzo</p>
              </footer>
            </>
          ) : projects ? (
            <>
              <Project />
              <footer>
                <button onClick={(e) => screenTittleButton(e)} id='accueil'>Accueil</button>
                <button onClick={(e) => profileButton(e)}>Profil</button>
                <button onClick={(e) => contactButton(e)}>Contact</button>
                <img src='./Image/Falla_Enzo_pic.png' alt='Profil' className='footer-image'></img>
                <p className="footer-name">Falla Enzo</p>
              </footer>
            </>
          ) : contact ? (
            <>
            <Contact />
            <footer>
              <button onClick={(e) => screenTittleButton(e)} id='accueil'>Accueil</button>
              <button onClick={(e) => profileButton(e)}>Profil</button>
              <button onClick={(e) => projectsButton(e)}>Projets</button>
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
