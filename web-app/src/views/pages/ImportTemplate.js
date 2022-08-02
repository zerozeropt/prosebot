import { CCard, CForm, CCardHeader, CCardBody, CFormInput, CFormSelect, CCardFooter, CButton, CRow, CCol } from '@coreui/react';
import axios from 'axios';
import React, { useState, useEffect } from 'react';

const ImportTemplate = () => {
    const [file, setFile] = useState();
    const [filename, setFilename] = useState("");
    const [contexts, setContexts] = useState([]);
    const [activeContext, setActiveContext] = useState();
    const [languages, setLanguages] = useState([]);
    const [activeLanguage, setActiveLanguage] = useState();
    const [uploaded, setUploaded] = useState(false);
    const [errors, setErrors] = useState("");

    const fileHandler = (event) => {
        setUploaded(false);
        const reader = new FileReader();
        reader.onload = function (e) {
            const content = e.target.result;
            setFile(JSON.parse(content));
        };
        const fileTmp = event.target.files[0];
        reader.readAsText(fileTmp);
        setFilename(fileTmp.name);
    }

    const doSubmit = (event) => {
        event.preventDefault();
        axios
            .post(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates`, {
                data: file,
                filename: filename
            }, {
                params: {
                    context: activeContext,
                    lang: activeLanguage
                }
            })
            .then(() => {
                setUploaded(true);
            })
            .catch((e) => {
                setErrors(e["message"]);
            });
    };

    useEffect(() => {
        axios
            .get(`${process.env.REACT_APP_VALIDATION_SERVER}/api/properties/contexts`)
            .then(async (res) => {
                const tmpContexts = res.data;
                setContexts(tmpContexts);
                if (tmpContexts.length > 0)
                    setActiveContext(tmpContexts[0]);
            })
            .catch((e) => {
                setErrors(e["message"]);
            });

        axios
            .get(`${process.env.REACT_APP_VALIDATION_SERVER}/api/properties/languages`)
            .then(async (res) => {
                const tmpLanguages = res.data;
                setLanguages(tmpLanguages);
                if (tmpLanguages.length > 0)
                    setActiveLanguage(tmpLanguages[0]);
            })
            .catch((e) => {
                setErrors(e["message"]);
            });
    }, []);

    return (
        <>
            {errors !== "" && (
                <CCard color='danger' style={{ marginBottom: "1rem", color: "white" }}>
                    <CCardHeader>
                        {errors}. Please refresh and try again.
                    </CCardHeader>
                </CCard>
            )}
            <CForm onSubmit={doSubmit}>
                <CCard>
                    <CCardHeader>
                        <h3>Import file</h3>
                    </CCardHeader>
                    <CCardBody>
                        <CFormInput type='file' label="Template json file" onChange={fileHandler} />
                        <CRow className='mt-2'>
                            <CCol>
                                <CFormSelect
                                    className="form-select"
                                    onChange={(opt) => {
                                        setActiveContext(opt.target.value);
                                    }}
                                >
                                    {contexts && contexts.map((context, i) => {
                                        return (
                                            <option key={`context-${i}`}>{context}</option>
                                        );
                                    })}
                                </CFormSelect>
                            </CCol>
                            <CCol>
                                <CFormSelect
                                    className="form-select"
                                    onChange={(opt) => {
                                        setActiveLanguage(opt.target.value);
                                    }}
                                >
                                    {languages && languages.map((context, i) => {
                                        return (
                                            <option key={`language-${i}`}>{context}</option>
                                        );
                                    })}
                                </CFormSelect>
                            </CCol>
                        </CRow>
                    </CCardBody>
                    <CCardFooter className='d-flex'>
                        {uploaded && (
                            <p className='m-0' style={{ color: "#54b5b0" }}>File successfully uploaded!</p>
                        )}
                        <CButton color='primary' type='submit' className='ms-auto'>
                            Save
                        </CButton>
                    </CCardFooter>
                </CCard>
            </CForm>
        </>
    )
}

export default ImportTemplate
