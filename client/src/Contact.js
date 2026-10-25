import React, { useState, useEffect } from 'react';
import config from "./config.js";

function Contact() {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [lastSubmitTime, setLastSubmitTime] = useState(null);
  const [timeLeft, setTimeLeft] = useState(0);
  const [formData, setFormData] = useState({
    from: '',
    subject: '',
    message: '',
  });

  useEffect(() => {
    if (lastSubmitTime) {
      const interval = setInterval(() => {
        const now = new Date().getTime();
        const remainingTime = Math.max(60 - Math.floor((now - lastSubmitTime) / 1000), 0);
        setTimeLeft(remainingTime);
        if (remainingTime === 0) {
          clearInterval(interval);
        }
      }, 1000);

      return () => clearInterval(interval);
    }
  }, [lastSubmitTime]);

  const change = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };

  const sendEmail = async (e) => {
    e.preventDefault();

    const now = Date.now();

    if (lastSubmitTime && now - lastSubmitTime < 60000) {
      alert("Veuillez attendre avant de renvoyer un message.");
      return;
    }

    setIsSubmitting(true);

    try {
      const response = await fetch(`${config.apiUrl}/api/mail`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json().catch(() => null);

      if (!response.ok) {
        console.error('Erreur API :', response.status, data);
        alert(data?.error || `Erreur serveur : ${response.status}`);
        return;
      }

      setLastSubmitTime(Date.now());
      setFormData({ from: '', subject: '', message: '' });
      alert('Email envoyé.');
    } catch (error) {
      console.error("Erreur réseau :", error);
      alert("Impossible de contacter le serveur.");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="contact-container">
      <form onSubmit={sendEmail}>
        <h2>Me contacter</h2>
        <div>
          <label htmlFor="from">Email :</label>
          <input type="email" placeholder="Votre adresse mail" onChange={change} name="from" id="from" required />
        </div>
        <div>
          <label htmlFor="subject">Objet :</label>
          <input placeholder="Objet" onChange={change} name="subject" id="subject" />
        </div>
        <div>
          <label htmlFor="message">Message :</label>
          <textarea placeholder="Votre message" onChange={change} name="message" id="message" required />
        </div>
        <button disabled={isSubmitting || timeLeft > 0} id="send">Envoyer</button>
        {timeLeft > 0 && <p>Veuillez patienter {timeLeft} secondes avant d'envoyer un nouveau message.</p>}
      </form>
    </div>
  );
}

export default Contact;
