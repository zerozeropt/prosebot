import React, { useEffect, useState } from 'react'
import axios from 'axios'
import {
  CButton,
  CCard,
  CCardBody,
  CCardFooter,
  CCardHeader,
  CCol,
  CForm,
  CFormInput,
  CFormSelect,
  CRow,
} from '@coreui/react';
import TemplateKey from '../../components/TemplateKey';
import TemplateFileButton from '../../components/TemplateFileButton';
import CIcon from '@coreui/icons-react';
import { cilX } from '@coreui/icons';

const Dashboard = () => {
  const [contexts, setContexts] = useState([]);
  const [activeContext, setActiveContext] = useState();
  const [languages, setLanguages] = useState([]);
  const [activeLanguage, setActiveLanguage] = useState();
  const [templates, setTemplates] = useState([]);
  const [activeFileIndex, setActiveFileIndex] = useState(-1);
  const [activeTemplateFile, setActiveTemplateFile] = useState();
  const [activeTemplateKey, setActiveTemplateKey] = useState("");
  const [addedTemplate, setAddedTemplate] = useState(false);
  const [addedTemplateKey, setAddedTemplateKey] = useState("template");
  const [isAddMode, setIsAddMode] = useState(false);
  const [isAddFileMode, setIsAddFileMode] = useState(false);
  const [tmpFileName, setTmpFileName] = useState("filename");
  const [errorsFile, setErrorsFile] = useState();
  const [errorsKey, setErrorsKey] = useState();
  const [validationErrors, setValidationErrors] = useState([]);
  const [showValidation, setShowValidation] = useState(false);
  const [errors, setErrors] = useState("");
  const colors = [
    "#3961b0",
    "#54b5b0",
  ];

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

  useEffect(() => {
    if (activeContext && activeLanguage)
      axios
        .get(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates/names`, {
          params: {
            context: activeContext,
            lang: activeLanguage
          }
        })
        .then(async (res) => {
          setTemplates(res.data);
        })
        .catch((e) => {
          setErrors(e["message"]);
        });
  }, [activeContext, activeLanguage]);

  useEffect(() => {
    if (activeFileIndex !== -1 && activeContext && activeLanguage)
      axios
        .get(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates/${templates[activeFileIndex]}/data`, {
          params: {
            context: activeContext,
            lang: activeLanguage
          }
        })
        .then(async (res) => {
          setActiveTemplateFile(res.data);
        })
        .catch((e) => {
          setErrors(e["message"]);
        });
  }, [activeFileIndex, activeContext, activeLanguage]);

  const resetChoices = () => {
    setActiveFileIndex(-1);
    setActiveTemplateFile();
    setAddedTemplate(false);
  }

  const onSaveEditTemplate = (key, index, text, condition) => {
    const tmpTemplates = activeTemplateFile;
    if (index === -1) {
      tmpTemplates[key].push({
        text: text,
        condition: condition
      })
    }
    else {
      tmpTemplates[key][index] = {
        text: text,
        condition: condition
      }
    }

    axios
      .put(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates/${templates[activeFileIndex]}/data`, {
        data: tmpTemplates,
      }, {
        params: {
          context: activeContext,
          lang: activeLanguage,
        }
      })
      .then(() => {
        setAddedTemplate(false);
        setActiveTemplateFile((oldItems) => {
          return {
            ...oldItems
          }
        });
      })
      .catch((e) => {
        setErrors(e["message"]);
      });
  }

  const onDeleteTemplate = (key, index) => {
    const tmpTemplates = activeTemplateFile;
    tmpTemplates[key].splice(index, 1);

    axios
      .put(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates/${templates[activeFileIndex]}/data`, {
        data: tmpTemplates,
      }, {
        params: {
          context: activeContext,
          lang: activeLanguage,
        }
      })
      .then((res) => {
        setActiveTemplateFile(res.data);
      })
      .catch((e) => {
        setErrors(e["message"]);
      });
  }

  const onAddTemplateKey = () => {
    const tmpTemplates = activeTemplateFile;
    if (tmpTemplates.hasOwnProperty(addedTemplateKey)) {
      setErrorsKey("Key already exists!");
      return;
    }
    tmpTemplates[addedTemplateKey] = [];

    axios
      .put(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates/${templates[activeFileIndex]}/data`, {
        data: tmpTemplates,
      }, {
        params: {
          context: activeContext,
          lang: activeLanguage
        }
      })
      .then(() => {
        setIsAddMode(false);
      })
      .catch((e) => {
        setErrors(e["message"]);
      });
  }

  const onEditTemplateKey = (event, tkey, newKey, isDelete = false) => {
    event.preventDefault();
    const tmpTemplates = activeTemplateFile;
    if (!isDelete)
      tmpTemplates[newKey] = tmpTemplates[tkey];
    if (newKey != tkey || isDelete)
      delete tmpTemplates[tkey];

    axios
      .put(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates/${templates[activeFileIndex]}/data`, {
        data: tmpTemplates,
      }, {
        params: {
          context: activeContext,
          lang: activeLanguage
        }
      })
      .then(() => {
        setActiveTemplateFile((oldItems) => {
          return {
            ...oldItems
          }
        });
      })
      .catch((e) => {
        setErrors(e["message"]);
      });
  }

  const onAddFile = (event) => {
    event.preventDefault();

    if (templates.includes(tmpFileName)) {
      setErrorsFile("File already exists!");
      return;
    }

    axios
      .post(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates`, {
        data: {
          entry_point: []
        },
        filename: tmpFileName
      }, {
        params: {
          context: activeContext,
          lang: activeLanguage
        }
      })
      .then(() => {
        setTemplates((oldItems) => [...oldItems, tmpFileName]);
        setIsAddFileMode(false);
        setTmpFileName("filename");
      })
      .catch((e) => {
        setErrors(e["message"]);
      });
  }

  const onDeleteFile = (event, filename, index) => {
    event.preventDefault();
    axios
      .delete(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates/${filename}`, {
        params: {
          context: activeContext,
          lang: activeLanguage
        }
      })
      .then(() => {
        setTemplates((oldItems) => oldItems.filter((item) => {
          return item !== filename
        }))
      })
      .catch((e) => {
        setErrors(e["message"]);
      });
  }

  const onRenameFile = (event, index, filename, newFilename) => {
    event.preventDefault();
    axios
      .put(`${process.env.REACT_APP_TEMPLATES_SERVER}/api/templates/${filename}/name`, {
        filename: newFilename,
      }, {
        params: {
          context: activeContext,
          lang: activeLanguage
        }
      })
      .then(() => {
        setTemplates((oldItems) => {
          return [...oldItems.slice(0, index), newFilename, ...oldItems.slice(index + 1)];
        })
      })
      .catch((e) => {
        setErrors(e["message"]);
      });
  }

  const onClickTemplateFileButton = (index) => {
    setActiveFileIndex(index);
    setShowValidation(false);
  }

  const onValidateFile = () => {
    axios
      .post(`${process.env.REACT_APP_VALIDATION_SERVER}/api/validator/file`, {
        data: activeTemplateFile,
      }, {
        params: {
          context: activeContext,
          lang: activeLanguage
        }
      })
      .then((res) => {
        setValidationErrors(res.data === "" ? [] : res.data.split("<br>"));
        setShowValidation(true);
      })
      .catch((e) => {
        setErrors(e["message"]);
      });
  }

  const onOpenTemplateKey = (tkeyName) => {
    setActiveTemplateKey(tkeyName != activeTemplateKey ? tkeyName : "");
  }

  return (
    <>
      {errors !== "" && (
        <CCard color='danger' style={{ marginBottom: "1rem", color: "white" }}>
          <CCardHeader>
            {errors}. Please refresh and try again.
          </CCardHeader>
        </CCard>
      )}
      <CRow>
        <CCol xs={9}>
          {activeTemplateFile && (
            <CCard>
              <CCardHeader className='d-flex pt-3 pb-3'>
                <h3>{templates[activeFileIndex]}</h3>
                {!showValidation && (
                  <CButton className='ms-auto' color='primary' onClick={onValidateFile}>
                    Validate file
                  </CButton>
                )}
                {showValidation && (
                  <CButton className='ms-auto' color='primary' onClick={() => setShowValidation(false)}>
                    <CIcon icon={cilX} />
                  </CButton>
                )}
              </CCardHeader>
              <CCardBody>
                {showValidation && (
                  <CCard className='mb-3'>
                    <CCardBody>
                      {validationErrors.length > 0 && validationErrors.map((error, i) => {
                        return (
                          <p style={{ color: "red" }} key={`error-${i}`}>{error}</p>
                        );
                      })}
                      {validationErrors.length === 0 && (
                        <p>No errors found</p>
                      )}
                    </CCardBody>
                  </CCard>
                )}
                <CCard style={{ border: "0" }}>
                  {Object.keys(activeTemplateFile).map((template, i) => {
                    return (
                      <TemplateKey
                        opened={activeTemplateKey === template}
                        activeContext={activeContext}
                        activeLanguage={activeLanguage}
                        key={`template-key-${template}-${i}`}
                        tkey={template}
                        color={colors[i % colors.length]}
                        activeTemplateFile={activeTemplateFile}
                        addedTemplate={addedTemplate}
                        onOpenTemplateKey={onOpenTemplateKey}
                        onEditTemplateKey={onEditTemplateKey}
                        setAddedTemplate={setAddedTemplate}
                        onSaveEditTemplate={onSaveEditTemplate}
                        onDeleteTemplate={onDeleteTemplate}
                      />
                    );
                  })}
                  {isAddMode && (
                    <CForm>
                      <CFormInput
                        type="text"
                        id="keyForm"
                        defaultValue="template"
                        onChange={(event) => setAddedTemplateKey(event.target.value)}
                      />
                    </CForm>
                  )}
                </CCard>
              </CCardBody>
              <CCardFooter>
                {!isAddMode && (
                  <CButton color='primary' onClick={() => setIsAddMode(true)}>
                    Add key +
                  </CButton>
                )}
                {isAddMode && (
                  <CRow>
                    <CCol className='ms-1' style={{ color: "red" }}>
                      {errorsKey}
                    </CCol>
                    <CCol className='d-flex justify-content-end mt-2 mb-2'>
                      <CButton color='secondary' className='me-1' onClick={() => setIsAddMode(false)}>
                        Cancel
                      </CButton>
                      <CButton onClick={onAddTemplateKey}>
                        Save
                      </CButton>
                    </CCol>
                  </CRow>
                )}
              </CCardFooter>
            </CCard>
          )}
        </CCol>
        <CCol xs={3} className='d-flex flex-column'>
          <CForm>
            <CFormSelect
              className="form-select mb-2"
              onChange={(opt) => {
                resetChoices();
                setActiveContext(opt.target.value);
              }}
            >
              {contexts && contexts.map((context, i) => {
                return (
                  <option key={`context-${i}`}>{context}</option>
                );
              })}
            </CFormSelect>
            <CFormSelect
              className="form-select mb-2"
              onChange={(opt) => {
                resetChoices();
                setActiveLanguage(opt.target.value);
              }}
            >
              {languages && languages.map((context, i) => {
                return (
                  <option key={`language-${i}`}>{context}</option>
                );
              })}
            </CFormSelect>
          </CForm>
          {templates && templates.map((template, i) => {
            return (
              <TemplateFileButton
                key={`template-${template}-${i}`}
                filename={template}
                tindex={i}
                setAddedTemplate={setAddedTemplate}
                activeFileIndex={activeFileIndex}
                onClick={onClickTemplateFileButton}
                onDeleteFile={onDeleteFile}
                onRenameFile={onRenameFile}
                resetChoices={resetChoices}
              />
            );
          })}
          {!isAddFileMode && (
            <CButton
              color='secondary'
              className='mt-1 w-100'
              onClick={() => setIsAddFileMode(true)}
            >
              Add file +
            </CButton>
          )}
          {isAddFileMode && (
            <CForm onSubmit={onAddFile}>
              {errorsFile}
              <CFormInput
                type="text"
                id="fileForm"
                className='mt-1'
                defaultValue={tmpFileName}
                onChange={(event) => setTmpFileName(event.target.value)}
              />
              <CButton type='submit' className='float-end mt-2'>
                Save
              </CButton>
              <CButton
                color='secondary'
                className='float-end m-2'
                onClick={() => {
                  setTmpFileName("filename");
                  setIsAddFileMode(false);
                }}
              >
                Cancel
              </CButton>
            </CForm>
          )}
        </CCol>
      </CRow>
    </>
  )
}

export default Dashboard
