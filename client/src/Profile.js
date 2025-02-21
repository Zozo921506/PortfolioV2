function Profile() {
  return (
    <div className="profile-container">
        <div id='profil-top'>
            <h1 className="title">Mon profil</h1>
            <img src='./Image/shiona.png' alt='Shiona' className='profile-image'></img>
            <h2>Enzo Falla</h2>
        </div>
            <div className='profile-content'>
            <h3>Description</h3>
            <p>Contrat recherché + présentation</p>
            <a href='./CV_Falla_Enzo.pdf' title='Téléchargement CV' download='CV_Falla_Enzo' id='resume'><strong>CV</strong></a>
        </div>
        <div className='profile-content'>
            <h3>Compétences</h3>
            <div className="profile-skill">
                <div className="skill-center">
                    <h4 className="skill-category">Front-end</h4>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                            <p>test</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                            <p>beep</p>
                        </div>
                    </div>
                </div>
                <div className="skill-center">
                    <h4 className="skill-category">Back-end</h4>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                            <p>test</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                            <p>beep</p>
                        </div>
                    </div>
                </div>
                <div className="skill-center">
                    <h4 className="skill-category">Outils</h4>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                            <p>test</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                            <p>beep</p>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
        <div className='profile-content'>
            <h3>Diplôme</h3>
            <p>Diplôme ici</p>
        </div>
        <div className='profile-content'>
            <h3>Langues</h3>
            <p>Langues parlé</p>
        </div>
        <div className='profile-content'>
            <h3>Hobbies</h3>
            <div className="profile-hobbies">
                <div className="hobby">
                    <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                    <p>Jeux Vidéo</p>
                </div>
                <div className="hobby">
                    <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                    <p>Manga/Animés</p>
                </div>
                <div className="hobby">
                    <img src='./Image/shiona.png' alt='Shiona' className='profile-pics'></img>
                    <p>Musique Japonaise</p>
                </div>
            </div>
        </div>
    </div>
  );
}

export default Profile;
