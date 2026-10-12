import React, { useState, useEffect } from 'react';
import config from "./config.js";

function Login() {
    const [formData, setFormData] = useState({
    email: '',
    password: ''
    });

    const [formDataRegister, setFormDataRegister] = useState({
        firstname: '',
        lastname: '',
        email: '',
        password: ''
    });

    const [isRegistering, setIsRegistering] = useState(false);

    const getLogged = async (e) => {
        e.preventDefault();
        const url = `${config.apiUrl}/api/login`;
        try 
        {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },

                body: JSON.stringify(formData),
            });

            if (response.ok) {
                const data = await response.json();
                const token = data.token;
                const cleanToken = token.replace(/^Bearer\s+/i, '');
                sessionStorage.setItem("token", cleanToken);
                console.log(cleanToken);
                alert("Vous pouvez maintenant voir et écrire un message");
            }
        }
        catch (e)
        {
            console.error(e);
        }
    }

    const register = async (e) => {
        e.preventDefault();
        const url = `${config.apiUrl}/api/register`;
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },

                body: JSON.stringify(formDataRegister),
            });

            if (response.ok) {
                alert("Utilisateur crée avec succès !");
                setIsRegistering(false);
            }
        } catch (e) {
            console.error(e);
        }
    }

    const change = (e) => {
        const { name, value } = e.target;
        setFormData({
        ...formData,
        [name]: value,
        });
    };

    const changeRegister = (e) => {
        const { name, value } = e.target;
        setFormDataRegister({
        ...formDataRegister,
        [name]: value,
        });
    };

    const registerPage = (e) => {
        e.preventDefault();
        setIsRegistering(true);
    }

    const loginPage = (e) => {
        e.preventDefault();
        setIsRegistering(false);
    }

    return (
        <>
            {!isRegistering ? (
                <div>
                    <h1 className='login_register_title'>Connexion</h1>
                    <form onSubmit={getLogged} className='login_form'>
                        <div className='login_form_containter'>
                            <label htmlFor="email" className='login_register_label'>Email:</label>
                            <input type='email' id='email' name='email' onChange={change}/>
                        </div>
                        <div className='login_form_containter'>
                            <label htmlFor="password" className='login_register_label'>Mot de passe:</label>
                            <input type='password' id='password' name='password' onChange={change}/>
                        </div>
                        <button className='login_button'>Connexion</button>
                    </form>
                    <button onClick={(e) => registerPage(e)} className='login_change_page'>Pas de compte? Inscrivez-vous</button>
                </div>
            ) : (
                <div>
                    <h1 className='login_register_title'>Inscription</h1>
                    <form onSubmit={register} className='register_form'>
                        <div className='register_row_container'>
                            <div className='register_form_container'>
                                <label htmlFor='firstname' className='login_register_label'>Prénom:</label>
                                <input type='text' name='firstname' id='firstname' onChange={changeRegister}/>
                            </div>
                            <div className='register_form_container'>
                                <label htmlFor='lastname' className='login_register_label'>Nom:</label>
                                <input type='text' name='lastname' id='lastname' onChange={changeRegister}/>
                            </div>
                        </div>
                        <div className='register_row_container'>
                            <div className='register_form_container'>
                                <label htmlFor='email' className='login_register_label'>Email:</label>
                                <input type='email' name='email' id='email' onChange={changeRegister}/>
                            </div>
                            <div className='register_form_container'>
                                <label htmlFor='password' className='login_register_label'>Mot de passe:</label>
                                <input type='password' name='password' id='password' onChange={changeRegister}/>
                            </div>
                        </div>
                        <button className='register_button'>Inscription</button>
                    </form>
                    <button onClick={(e) => loginPage(e)} className='register_change_page'>Vous avez déjà un compte? Connectez-vous</button>
                </div>
            )}
        </>
    )
}

export default Login;