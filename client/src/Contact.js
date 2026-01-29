import React, { useState, useEffect } from 'react';

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

    const now = new Date().getTime();
    if (lastSubmitTime && now - lastSubmitTime < 60000) {
      alert("Veuillez attendre avant de renvoyer un message.");
      return;
    }

    setIsSubmitting(true);
    setLastSubmitTime(now);

    const url = "https://vps-48914820.vps.ovh.net/api/mail";
    try {
      const response = await fetch(url, {
        method: 'POST',
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        alert("Email envoyé");
        setIsSubmitting(false);
      }
      else {
        console.log("Une erreur est survenue");
      }
    }
    catch (e) {
      console.error(e);
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
