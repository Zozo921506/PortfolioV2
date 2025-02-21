import React, { useState } from 'react';

function Contact() {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [lastSubmitTime, setLastSubmitTime] = useState(null);
  const [formData, setFormData] = useState(
    {
      from: '',
      subject: '',
      message: '',

    }
  );

  const change = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData, [name]: value
    });
  };

  const sendEmail = async (e) => {
    e.preventDefault();

    const now = new Date().getTime();
    if (lastSubmitTime && now - lastSubmitTime < 60000) 
    {
      alert("Veuillez attendre avant de renvoyer un message.");
      return;
    }

    setIsSubmitting(true);
    setLastSubmitTime(now);

    const url = "http://localhost:8000/api/mail";
    try
    {
      const response = await fetch(url, {
        method: 'POST',
        body: JSON.stringify(formData)
      });

      if (response.ok)
      {
        alert("Email envoyé");
        setIsSubmitting(false);
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

  return (
    <div className="contact-container">
      <form onSubmit={(e) => sendEmail(e)}>
        <h2>Me contacter</h2>
        <div>
          <label htmlFor="from">Email :</label>
          <input type="email" placeholder="Votre adresse mail" onChange={(e) => change(e)} name="from" id="from" required />
        </div>
        <div>
          <label htmlFor="subject">Objet :</label>
          <input placeholder="Objet" onChange={(e) => change(e)} name="subject" id="subject" />
        </div>
        <div>
          <label htmlFor="message">Message :</label>
          <textarea placeholder="Votre message" onChange={(e) => change(e)} name="message" id="message" required />
        </div>
        <button disabled={isSubmitting} id="send">Envoyer</button>
      </form>
    </div>
  );
}

export default Contact;