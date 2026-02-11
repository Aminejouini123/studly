import React, { useState } from 'react';

const CourseForm = () => {
  const [formData, setFormData] = useState({
    name: '',
    teacherEmail: '',
    courseLink: '',
    semester: '',
    difficultyLevel: '',
    type: '',
    status: 'en_attente',
    priority: 'basse',
    coefficient: 1.0,
    duration: '',
    courseFile: null,
    comment: ''
  });

  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitStatus, setSubmitStatus] = useState(null); // 'success' | 'error'

  const handleChange = (e) => {
    const { name, value, type, files } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'file' ? files[0] : value
    }));
    // Clear error when user types
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const validate = () => {
    const newErrors = {};
    if (!formData.name.trim()) newErrors.name = "Le nom du cours est requis.";
    if (!formData.teacherEmail.trim()) {
      newErrors.teacherEmail = "L'email le professeur est requis.";
    } else if (!/\S+@\S+\.\S+/.test(formData.teacherEmail)) {
      newErrors.teacherEmail = "L'email n'est pas valide.";
    }
    if (formData.courseLink && !/^https?:\/\//.test(formData.courseLink)) {
        newErrors.courseLink = "Le lien doit commencer par http:// ou https://";
    }
    if (!formData.duration || formData.duration <= 0) {
        newErrors.duration = "La durée doit être supérieure à 0.";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) return;

    setIsSubmitting(true);
    setSubmitStatus(null);

    // Mock submission
    try {
      await new Promise(resolve => setTimeout(resolve, 1500)); // Simulate API call calls
      console.log("Form Data Submitted:", formData);
      setSubmitStatus('success');
      // Reset form or redirect here
    } catch (error) {
      console.error("Submission error:", error);
      setSubmitStatus('error');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="container py-5">
      <div className="card shadow-sm border-0 rounded-4">
        <div className="card-header bg-primary text-white p-4 rounded-top-4">
            <h2 className="mb-0">Ajouter un Nouveau Cours</h2>
            <p className="mb-0 opacity-75">Version React Component</p>
        </div>
        <div className="card-body p-4 p-md-5">
          {submitStatus === 'success' && (
            <div className="alert alert-success mb-4" role="alert">
              Cours créé avec succès !
            </div>
          )}
          {submitStatus === 'error' && (
            <div className="alert alert-danger mb-4" role="alert">
              Une erreur est survenue lors de la création du cours.
            </div>
          )}

          <form onSubmit={handleSubmit} noValidate>
            
            {/* General Info */}
            <h4 className="text-primary mb-3"><i className="bi bi-info-circle me-2"></i>Informations Générales</h4>
            <div className="row g-3 mb-4">
              <div className="col-md-6">
                <label htmlFor="name" className="form-label fw-bold small text-uppercase text-muted">Nom du Cours</label>
                <input
                  type="text"
                  className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                  id="name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  placeholder="Ex: Algèbre Linéaire"
                  required
                />
                <div className="invalid-feedback">{errors.name}</div>
              </div>

              <div className="col-md-6">
                <label htmlFor="teacherEmail" className="form-label fw-bold small text-uppercase text-muted">Email du Professeur</label>
                <div className="input-group has-validation">
                    <span className="input-group-text bg-light"><i className="bi bi-envelope"></i></span>
                    <input
                    type="email"
                    className={`form-control ${errors.teacherEmail ? 'is-invalid' : ''}`}
                    id="teacherEmail"
                    name="teacherEmail"
                    value={formData.teacherEmail}
                    onChange={handleChange}
                    placeholder="prof@example.com"
                    required
                    />
                    <div className="invalid-feedback">{errors.teacherEmail}</div>
                </div>
              </div>
                
                 <div className="col-md-6">
                <label htmlFor="courseLink" className="form-label fw-bold small text-uppercase text-muted">Lien Visio</label>
                 <div className="input-group has-validation">
                    <span className="input-group-text bg-light"><i className="bi bi-link"></i></span>
                    <input
                    type="url"
                    className={`form-control ${errors.courseLink ? 'is-invalid' : ''}`}
                    id="courseLink"
                    name="courseLink"
                    value={formData.courseLink}
                    onChange={handleChange}
                    placeholder="https://..."
                    />
                    <div className="invalid-feedback">{errors.courseLink}</div>
                 </div>
              </div>
            </div>

            {/* Details */}
            <div className="border-top pt-4 mb-4">
                 <h4 className="text-primary mb-3"><i className="bi bi-sliders me-2"></i>Détails</h4>
                 <div className="row g-3">
                    <div className="col-md-4">
                        <label htmlFor="semester" className="form-label fw-bold small text-uppercase text-muted">Semestre</label>
                        <select
                            className="form-select"
                            id="semester"
                            name="semester"
                            value={formData.semester}
                            onChange={handleChange}
                        >
                            <option value="">Choisir...</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                        </select>
                    </div>
                     <div className="col-md-4">
                        <label htmlFor="difficultyLevel" className="form-label fw-bold small text-uppercase text-muted">Difficulté</label>
                        <select
                            className="form-select"
                            id="difficultyLevel"
                            name="difficultyLevel"
                            value={formData.difficultyLevel}
                            onChange={handleChange}
                        >
                             <option value="">Choisir...</option>
                            <option value="facile">Facile</option>
                            <option value="moyen">Moyen</option>
                             <option value="difficile">Difficile</option>
                        </select>
                    </div>
                     <div className="col-md-4">
                        <label htmlFor="type" className="form-label fw-bold small text-uppercase text-muted">Type</label>
                        <select
                            className="form-select"
                            id="type"
                            name="type"
                            value={formData.type}
                            onChange={handleChange}
                        >
                             <option value="">Choisir...</option>
                            <option value="magistral">Magistral</option>
                            <option value="td">TD</option>
                            <option value="tp">TP</option>
                        </select>
                    </div>
                     <div className="col-md-4">
                        <label htmlFor="duration" className="form-label fw-bold small text-uppercase text-muted">Durée (h)</label>
                         <input
                            type="number"
                            className={`form-control ${errors.duration ? 'is-invalid' : ''}`}
                            id="duration"
                            name="duration"
                            value={formData.duration}
                            onChange={handleChange}
                            min="1"
                        />
                         <div className="invalid-feedback">{errors.duration}</div>
                    </div>
                     <div className="col-md-4">
                        <label htmlFor="coefficient" className="form-label fw-bold small text-uppercase text-muted">Coefficient</label>
                         <input
                            type="number"
                             className="form-control"
                            id="coefficient"
                            name="coefficient"
                            value={formData.coefficient}
                            onChange={handleChange}
                            step="0.5"
                        />
                    </div>
                 </div>
            </div>

            {/* Actions */}
            <div className="d-flex justify-content-end gap-2 border-top pt-4">
              <button type="button" className="btn btn-light px-4">Annuler</button>
              <button type="submit" className="btn btn-primary px-4" disabled={isSubmitting}>
                {isSubmitting ? (
                    <>
                    <span className="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                    <span role="status">Enregistrement...</span>
                    </>
                ) : (
                    <>
                    <i className="bi bi-save me-2"></i> Enregistrer
                    </>
                )}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default CourseForm;
