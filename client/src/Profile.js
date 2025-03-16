function Profile() {
  return (
    <div className="profile-container">
        <div id='profil-top'>
            <h1 className="title">Mon profil</h1>
            <img src='./Image/Falla_Enzo_pic.png' alt='Profil' className='profile-image'></img>
            <h2>Enzo Falla</h2>
        </div>
            <div className='profile-content'>
            <h3>Description</h3>
            <p>Je suis Falla Enzo étudiant de deuxième année à la Web@cadémie by EPITECH et je suis à la recherche d'une alternance dans le domaine du développement web full stack pour une durée de 9 mois</p>
            <a href='./CV_Falla_Enzo.pdf' title='Téléchargement CV' download='CV_Falla_Enzo' id='resume'><strong>CV</strong></a>
        </div>
        <div className='profile-content'>
            <h3>Compétences</h3>
            <div className="profile-skill">
                <div className="skill-center">
                    <h4 className="skill-category">Front-end</h4>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/html.svg' alt='HTML' className='profile-pics'></img>
                            <p>HTML</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/css.svg' alt='CSS' className='profile-pics'></img>
                            <p>CSS</p>
                        </div>
                    </div>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/javascript.svg' alt='JavaScript' className='profile-pics'></img>
                            <p>JavaScript</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/react.svg' alt='ReactJs' className='profile-pics'></img>
                            <p>ReactJs</p>
                        </div>
                    </div>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/vuejs.svg' alt='VueJs' className='profile-pics'></img>
                            <p>VueJs</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/twig.svg' alt='Twig' className='profile-pics'></img>
                            <p>Twig</p>
                        </div>
                    </div>
                </div>
                <div className="skill-center">
                    <h4 className="skill-category">Back-end</h4>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/php.svg' alt='PHP' className='profile-pics'></img>
                            <p>PHP</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/symfony.svg' alt='Symfony' className='profile-pics'></img>
                            <p>Symfony</p>
                        </div>
                    </div>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/laravel.svg' alt='Laravel' className='profile-pics'></img>
                            <p>Laravel</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/sql.svg' alt='SQL' className='profile-pics'></img>
                            <p>MySQL</p>
                        </div>
                    </div>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/mongodb.svg' alt='MongoDB' className='profile-pics'></img>
                            <p>MongoDB</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/nodejs.svg' alt='NodeJS' className='profile-pics'></img>
                            <p>NodeJS</p>
                        </div>
                    </div>
                </div>
                <div className="skill-center">
                    <h4 className="skill-category">Outils</h4>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/github.svg' alt='Github' className='profile-pics'></img>
                            <p>Github</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/docker.svg' alt='Docker' className='profile-pics'></img>
                            <p>Docker</p>
                        </div>
                    </div>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/figma.svg' alt='Figma' className='profile-pics'></img>
                            <p>Figma</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/trello.svg' alt='Trello' className='profile-pics'></img>
                            <p>Trello</p>
                        </div>
                    </div>
                    <div className="grid-skills">
                        <div className="grid-language">
                            <img src='./Image/vscode.png' alt='VsCode' className='profile-pics'></img>
                            <p>VsCode</p>
                        </div>
                        <div className="grid-language">
                            <img src='./Image/teams.svg' alt='Teams' className='profile-pics'></img>
                            <p>Teams</p>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
        <div className='profile-content'>
            <h3>Diplôme</h3>
            <p>Baccalauréat général spécialités Mathématiques/NSI</p>
        </div>
        <div className='profile-content'>
            <h3>Langues</h3>
            <ul>
                <li>Anglais</li>
                <li>Japonais(en cours d'apprentissage avec Duolingo)</li>
            </ul>
        </div>
        <div className='profile-content'>
            <h3>Hobbies</h3>
            <div className="profile-hobbies">
                <div className="hobby">
                    <img src='./Image/manette.svg' alt='Manette' className='profile-pics'></img>
                    <p>Jeux Vidéo</p>
                </div>
                <div className="hobby">
                    <img src='./Image/manga.svg' alt='Manga/Animés' className='profile-pics'></img>
                    <p>Manga/Animés</p>
                </div>
                <div className="hobby">
                    <img src='./Image/note-musical.png' alt='Musique Japonaise' className='profile-pics'></img>
                    <p>Musique Japonaise</p>
                </div>
            </div>
        </div>
    </div>
  );
}

export default Profile;
