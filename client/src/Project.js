import React, { useState, useEffect } from 'react';

function Project() {
  const [displayProjects, setDisplayProjects] = useState([]);
  const [selectedProject, setSelectedProject] = useState(null);
  const [search, setSearch] = useState("");
  const [errorApi, setErrorApi] = useState(false);

  const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
  };

  const filteredProjects = displayProjects.filter(project => {
    return project.name.toLowerCase().includes(search.toLowerCase()) || project.languages.some(language => language.toLowerCase().includes(search.toLowerCase()));
  });

  const inputChange = (e) =>
  {
    setSearch(e.target.value);
  }

  useEffect(() => {
    const apiProjects = [
      {id: 1, name: "My cinema", description: "Durant ce projet, je devais faire un site de cinéma où l'on pouvait rechercher un film par nom/catégories, de pouvoir voir la liste des membres et de leurs grades. Ainsi que de pouvoir modifier ces derniers, de plus, on devait pouvoir ajouter une séance.", languages: ["HTML", "CSS", "JavaScript", "PHP", "MySQL"], imageURL: "./Image/projets/cinema.png"},
      {id: 2, name: "Puissance 4", description: "Durant ce projet, je devais refaire un jeu de société iconique qui est le puissance 4. Je devais le faire en module. L'objectif de ce jeu est très simple, le premier joueur qui aligne horizontalement ou verticalement ou en diagonnales 4 pions de la même couleur remporte la partie.", languages: ["HTML", "CSS", "JavaScript"], imageURL: "./Image/projets/puissance4.png"},
      {id: 3, name: "E-commerce", description: "Durant ce projet, je devais faire un site ayant les fonctionnalités de e-commerce ayant pour contrainte de vérifier si l'utilisateur est majeur ou non. Si l'utilisateur est mineur, il ne peut pas créer de compte et accéder aux fonctionnalités qui sont la recherche par genre, par tranche d'âge et par localisation. Je devais également faire un système de connexion avec les informations de l'utilisateur.", languages: ["CSS", "ReactJs", "Symfony", "MySQL"], imageURL: "./Image/projets/e-commerce.png"},
      {id: 4, name: "Twitter", description: "Durant ce projet, je devais refaire twitter et ses fonctionnalités que se soit de pouvoir se connecter, tweeter, retweeter, répondre au tweet et au messages, liker un tweet, un tweet avec 140 caractères maximum, pouvoir voir ses abonnés et ses abonnement et rechercher par tags de l'utilisateur.", languages: ["HTML", "CSS", "JavaScript", "PHP", "MySQL"], imageURL: "./Image/projets/twitter.png"},
      {id: 5, name: "Spotify", description: "Durant ce projet, je devais reproduire certaines fonctionnalités de spotify qui sont la recherche, les albums et leurs détails, les genres et leurs détails et les artistes et leurs détails.", languages: ["CSS", "ReactJs", "Docker"], imageURL: "./Image/projets/spotify.png"},
      {id: 6, name: "PortfolioV1", description: "Ce projet est la première version de mon portfolio que j'ai effectué en première année", languages: ["HTML", "CSS", "JavaScript"], imageURL: "./Image/projets/portfolio.png"},
    ];

    const getProjects = async() => {
      const url = "http://localhost:8000/api/projects";
      try
      {
        const response = await fetch(url, {
          method: 'GET'
        });
  
        if (response.ok)
        {
          const data = await response.json();
          setDisplayProjects(data);
          if (data.length > 0)
          {
            setSelectedProject(data[0]);
          }
          console.log(data);
        }
        else
        {
          console.log("Une erreur est survenue");
        }
      }
      catch (e)
      {
        console.error(e);
        setErrorApi(true);
        setDisplayProjects(apiProjects)
        setSelectedProject(apiProjects[0]);
      }
    }
    if (!errorApi)
    {
      getProjects();
    }
  }, [errorApi]);
  return (
    <div className='project-container'>
        <div className='display-project'>
            <div className='project-details'>
              {errorApi ? (
              <>
                {selectedProject ? (
                <>
                  <img src={selectedProject.imageURL} alt={selectedProject.name} className='selected-pic'></img>
                  <h4>Titre: {selectedProject.name}</h4>
                  <p><strong>Description:</strong> {selectedProject.description}</p>
                  <div className='project-languages'>
                    <p><strong>Langages: </strong></p>
                    {selectedProject.languages.map((language) => (
                      <p key={language}>{language}</p>
                    ))}
                  </div>
                </>
              ) : (
                <p>Sélectionnez un projet pour voir les détails</p>
              )}
              </>) : (
                <>
                  {selectedProject ? (
                  <>
                    <img src={`https://vps-48914820.vps.ovh.net${selectedProject.imageUrl}`} alt={selectedProject.name} className='selected-pic'></img>
                    <h4>Titre: {selectedProject.name}</h4>
                    <p><strong>Description:</strong> {selectedProject.description}</p>
                    <div className='project-languages'>
                      <p><strong>Langages: </strong></p>
                      {selectedProject.languages.map((language) => (
                        <p key={language}>{language}</p>
                      ))}
                    </div>
                    <p><strong>Ajouté: </strong>{formatDate(selectedProject.created_at)}</p>
                    <p><strong>Dernière modification: </strong>{formatDate(selectedProject.updated_at)}</p>
                  </>
                ) : (
                  <p>Sélectionnez un projet pour voir les détails</p>
                )}
                </>
              )}
            </div>
            <div className='project-list'>
              <input placeholder='Rechercher des projets' onChange={(e) => inputChange(e)} value={search} id='searchbar'></input>
              {errorApi ? (
                <>
                  {search ? (
                  <>
                    {filteredProjects.length > 0 ? (
                      <>
                        {filteredProjects.map((project) => {
                          return <div key={project.id} className='project' onClick={() => setSelectedProject(project)}>
                            <img src={project.imageURL} alt={project.name} className='project-pic'></img>
                            <div className='project-value'>
                                <p className='project-name'>{project.name}</p>
                                <div className='project-languages'>
                                    {project.languages.map((language) => {
                                        return <p key={language}>{language}</p>
                                    })}
                                </div>
                            </div>
                          </div>
                        })}
                      </>
                    ) : (
                    <>
                      <p>Aucun projet ne correspond à votre recherche</p>
                    </>)}
                  </>
                ) : (
                  <>
                    {displayProjects ? displayProjects.map((project) => {
                    return <div key={project.id} className='project' onClick={() => setSelectedProject(project)}>
                        <img src={project.imageURL} alt={project.name} className='project-pic'></img>
                        <div className='project-value'>
                            <p className='project-name'>{project.name}</p>
                            <div className='project-languages'>
                                {project.languages.map((language) => {
                                    return <p key={language}>{language}</p>
                                })}
                            </div>
                        </div>
                    </div>
                    }) : (
                    <p>Pas de projet à montrer</p>
                )}
                  </>
                )}
                </>
              ) : (
                <>
                  {search ? (
                    <>
                      {filteredProjects.length > 0 ? (
                        <>
                          {filteredProjects.map((project) => {
                            return <div key={project.id} className='project' onClick={() => setSelectedProject(project)}>
                              <img src={`https://vps-48914820.vps.ovh.net${project.imageUrl}`} alt={project.name} className='project-pic'></img>
                              <div className='project-value'>
                                  <p className='project-name'>{project.name}</p>
                                  <div className='project-languages'>
                                      {project.languages.map((language) => {
                                          return <p key={language}>{language}</p>
                                      })}
                                  </div>
                              </div>
                            </div>
                          })}
                        </>
                      ) : (
                      <>
                        <p>Aucun projet ne correspond à votre recherche</p>
                      </>)}
                    </>
                  ) : (
                    <>
                      {displayProjects ? displayProjects.map((project) => {
                      return <div key={project.id} className='project' onClick={() => setSelectedProject(project)}>
                          <img src={`https://vps-48914820.vps.ovh.net${project.imageUrl}`} alt={project.name} className='project-pic'></img>
                          <div className='project-value'>
                              <p className='project-name'>{project.name}</p>
                              <div className='project-languages'>
                                  {project.languages.map((language) => {
                                      return <p key={language}>{language}</p>
                                  })}
                              </div>
                          </div>
                      </div>
                      }) : (
                      <p>Pas de projet à montrer</p>
                  )}
                    </>
                  )}
                </>
              )}
            </div>
        </div>
    </div>
  );
}

export default Project;
