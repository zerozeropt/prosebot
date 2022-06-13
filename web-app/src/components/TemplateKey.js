import React, { useState } from 'react'
import {
    CButton,
    CCollapse,
    CDropdown,
    CDropdownItem,
    CDropdownMenu,
    CDropdownToggle,
    CForm,
    CFormInput,
    CModal,
    CModalBody,
    CModalFooter
} from '@coreui/react';
import Template from './Template';

const TemplateKey = ({
    activeContext,
    activeLanguage,
    tkey,
    color,
    activeTemplateFile,
    addedTemplate,
    onEditTemplateKey,
    setAddedTemplate,
    onSaveEditTemplate,
    onDeleteTemplate
}) => {
    const [tmpKey, setTmpKey] = useState(tkey);
    const [opened, setOpened] = useState(false);
    const [isEditMode, setIsEditMode] = useState(false);
    const [deleteModal, openModal] = useState(false);

    return (
        <div>
            <div className='d-flex'>
                {!isEditMode && (
                    <>
                        <CButton
                            className='w-100 rounded-right'
                            style={{ backgroundColor: color, border: color, borderBottomRightRadius: 0, borderTopRightRadius: 0 }}
                            onClick={() => {
                                setOpened(!opened);
                                setAddedTemplate(false);
                            }}
                        >
                            {tkey}
                        </CButton>
                        <CDropdown>
                            <CDropdownToggle
                                style={{
                                    backgroundColor: color,
                                    border: color,
                                    borderBottomLeftRadius: 0,
                                    borderTopLeftRadius: 0
                                }}
                            />
                            <CDropdownMenu>
                                <CDropdownItem
                                    onClick={() => {
                                        setOpened(false);
                                        setIsEditMode(true);
                                    }}
                                >
                                    Edit
                                </CDropdownItem>
                                <CDropdownItem onClick={() => openModal(true)}>Delete</CDropdownItem>
                            </CDropdownMenu>
                        </CDropdown>
                    </>
                )}
                {isEditMode && (
                    <CForm
                        className='d-flex w-100'
                        onSubmit={(event) => {
                            setIsEditMode(false);
                            onEditTemplateKey(event, tkey, tmpKey);
                        }}
                    >
                        <CFormInput
                            type="text"
                            id="keyForm"
                            defaultValue={tmpKey}
                            onChange={(event) => setTmpKey(event.target.value)}
                        />
                        <CButton type='submit'>
                            Save
                        </CButton>
                    </CForm>
                )}
            </div>
            <CCollapse visible={opened} className='mb-5'>
                {activeTemplateFile[tkey] && activeTemplateFile[tkey].map((key, j) => {
                    return (
                        <Template
                            activeContext={activeContext}
                            activeLanguage={activeLanguage}
                            key={`${tkey}-${key["text"]}-${key["condition"]}-${j}`}
                            tkey={tkey}
                            tindex={j}
                            text={key["text"]}
                            condition={key["condition"]}
                            onSaveEditTemplate={onSaveEditTemplate}
                            onDeleteTemplate={onDeleteTemplate}
                        />
                    )
                })}
                {addedTemplate && (
                    <Template
                        activeContext={activeContext}
                        activeLanguage={activeLanguage}
                        tkey={tkey}
                        text={""}
                        condition={""}
                        mode
                        onSaveEditTemplate={onSaveEditTemplate}
                        setAddedTemplate={setAddedTemplate}
                    />
                )}
                {!addedTemplate && (
                    <CButton color='primary' onClick={() => setAddedTemplate(true)}>
                        Add template +
                    </CButton>
                )}
            </CCollapse>
            <CModal
                className="show"
                visible={deleteModal}
            >
                <CModalBody>
                    Are you sure you want to delete this key?
                </CModalBody>
                <CModalFooter>
                    <CButton color='secondary' onClick={() => openModal(false)}>Cancel</CButton>
                    <CButton
                        color='primary'
                        onClick={(e) => {
                            openModal(false);
                            onEditTemplateKey(e, tkey, tmpKey, true);
                        }}
                    >
                        Yes
                    </CButton>
                </CModalFooter>
            </CModal>
        </div>
    );
}

export default TemplateKey
