import React, { useState, useEffect } from 'react';

function Project() {
  const [displayProjects, setDisplayProjects] = useState([]);
  const [selectedProject, setSelectedProject] = useState(null);
  const [search, setSearch] = useState("");
  // const [projectsFound, setProjectsFound] = useState([]);

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
      }
      else
      {
        console.log("Une erreur est survenue");
      }
    }
    catch (e)
    {
      console.error(e);
    }
  }

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
    getProjects();
  }, []);
  return (
    <div className='project-container'>
        <input placeholder='Rechercher des projets' onChange={(e) => inputChange(e)} value={search} id='searchbar'></input>
        <div className='display-project'>
            <div className='project-details'>
              {selectedProject ? (
              <>
                <img src={selectedProject.imageUrl} alt={selectedProject.name} className='selected-pic'></img>
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
            </div>
            <div className='project-list'>
                {search ? (
                  <>
                    {filteredProjects.length > 0 ? (
                      <>
                        {filteredProjects.map((project) => {
                          return <div key={project.id} className='project' onClick={() => setSelectedProject(project)}>
                            <img src={`http://localhost:8000${project.imageUrl}`} alt={project.name} className='project-pic'></img>
                            <div className='project-value'>
                                <p>{project.name}</p>
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
                        <img src='./Image/shiona.png' alt='Shiona' className='project-pic'></img>
                        <div className='project-value'>
                            <p>{project.name}</p>
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
            </div>
        </div>
    </div>
  );
}

export default Project;
