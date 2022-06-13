import React, { useState } from 'react';
import { CButton, CCard, CCardBody, CCardFooter, CCol, CForm, CFormInput, CFormTextarea, CModal, CModalBody, CModalFooter, CRow } from '@coreui/react';
import CIcon from '@coreui/icons-react';
import {
    cilPencil,
    cilTrash,
    cilX
} from '@coreui/icons'
import axios from 'axios';

const Template = ({
    activeContext,
    activeLanguage,
    tkey,
    tindex = -1,
    text,
    condition,
    mode = false,
    onSaveEditTemplate,
    setAddedTemplate = () => { },
    onDeleteTemplate = () => { }
}) => {
    const [isEditMode, setMode] = useState(mode);
    const [deleteModal, openModal] = useState(false);
    const [tmpText, setTmpText] = useState(text);
    const [tmpCondition, setTmpCondition] = useState(condition);
    const [showValidation, setShowValidation] = useState(false);
    const [validationErrors, setValidationErrors] = useState([]);
    const [saveAnyway, setSaveAnyway] = useState(false);

    const onSave = () => {
        setMode(false);
        setShowValidation(false);
        onSaveEditTemplate(tkey, tindex, tmpText, tmpCondition);
    }

    const onValidate = (isSave = false) => {
        axios
            .post(`${process.env.REACT_APP_VALIDATION_SERVER}/api/validator/template.php`, {
                text: tmpText,
                condition: tmpCondition
            }, {
                params: {
                    context: activeContext,
                    lang: activeLanguage
                }
            })
            .then((res) => {
                if (isSave && res.data == "") {
                    onSave();
                }
                else {
                    setMode(false);
                    setShowValidation(true);
                    setValidationErrors(res.data == "" ? [] : res.data.split("<br>"));
                    if (isSave) setSaveAnyway(true);
                }
            })
    }

    const resetTextCondition = () => {
        setTmpText(text);
        setTmpCondition(condition);
        setSaveAnyway(false);
        setShowValidation(false);
    }

    return (
        <CCard className={"mb-4"}>
            {!isEditMode && (
                <>
                    <CCardBody>
                        {tmpText}
                    </CCardBody>
                    <CCardFooter>
                        <CRow>
                            <CCol xs={10}>
                                {tmpCondition !== "" ? "Condition: " + tmpCondition : "No condition"}
                            </CCol>
                            <CCol xs={2} className='d-flex flex-row-reverse'>
                                {!showValidation && (
                                    <CButton
                                        size='sm'
                                        color='primary'
                                        className='ms-2'
                                        onClick={() => onValidate()}
                                    >
                                        Validate
                                    </CButton>
                                )}
                                {showValidation && (
                                    <CButton
                                        size='sm'
                                        color='primary'
                                        className='ms-2'
                                        onClick={() => {
                                            setShowValidation(false);
                                            resetTextCondition();
                                            setAddedTemplate(false);
                                        }}
                                    >
                                        <CIcon icon={cilX} />
                                    </CButton>
                                )}
                                <CButton size='sm' color='secondary' className='me-1' onClick={() => openModal(true)}>
                                    <CIcon icon={cilTrash} />
                                </CButton>
                                <CButton size='sm' color='secondary' className='me-1' onClick={() => setMode(true)}>
                                    <CIcon icon={cilPencil} />
                                </CButton>
                            </CCol>
                        </CRow>
                    </CCardFooter>
                    {showValidation && (
                        <CCard>
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
                            {saveAnyway && (
                                <CCardFooter className='d-flex flex-row-reverse'>
                                    <CButton className='ms-2' onClick={() => {
                                        onSave();
                                        setSaveAnyway(false);
                                    }}>
                                        Save anyway
                                    </CButton>
                                    <CButton color='secondary' onClick={() => {
                                        resetTextCondition();
                                        setAddedTemplate(false);
                                    }}>
                                        Cancel
                                    </CButton>
                                </CCardFooter>
                            )}
                        </CCard>
                    )}
                </>
            )}
            {isEditMode && (
                <CForm>
                    <CCardBody>
                        <CFormTextarea
                            type="text"
                            id="textForm"
                            rows="4"
                            defaultValue={tmpText}
                            onChange={(event) => setTmpText(event.target.value)}
                        />
                    </CCardBody>
                    <CCardFooter>
                        <CRow>
                            <CCol lg={10} className='d-flex'>
                                Condition:
                                <CFormInput
                                    type='text'
                                    id="conditionForm"
                                    className='ms-1'
                                    defaultValue={tmpCondition}
                                    onChange={(event) => setTmpCondition(event.target.value)}
                                />
                            </CCol>
                            <CCol lg={2} className='d-flex flex-row-reverse'>
                                <CButton
                                    size='sm'
                                    color='primary'
                                    onClick={() => {
                                        onValidate(true);
                                    }}
                                >
                                    Save
                                </CButton>
                                <CButton
                                    color='secondary'
                                    className='me-1'
                                    onClick={() => {
                                        resetTextCondition();
                                        setMode(false);
                                        setAddedTemplate(false);
                                    }}
                                >
                                    Cancel
                                </CButton>
                            </CCol>
                        </CRow>
                    </CCardFooter>
                </CForm>
            )}
            <CModal
                className="show"
                visible={deleteModal}
            >
                <CModalBody>
                    Are you sure you want to delete this template?
                </CModalBody>
                <CModalFooter>
                    <CButton color='secondary' onClick={() => openModal(false)}>Cancel</CButton>
                    <CButton
                        color='primary'
                        onClick={() => {
                            openModal(false);
                            onDeleteTemplate(tkey, tindex);
                        }}
                    >
                        Yes
                    </CButton>
                </CModalFooter>
            </CModal>
        </CCard>
    )
}

export default Template
