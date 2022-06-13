import React, { useState } from 'react'
import {
  CButton,
  CDropdown,
  CDropdownMenu,
  CDropdownItem,
  CDropdownToggle,
  CModal,
  CModalBody,
  CModalFooter,
  CForm,
  CFormInput
} from '@coreui/react';

const TemplateFileButton = ({
  filename,
  tindex,
  setAddedTemplate,
  activeFileIndex,
  onClick,
  onDeleteFile,
  onRenameFile,
  resetChoices
}) => {
  const [tmpFileName, setTmpFileName] = useState(filename);
  const [isEditMode, setIsEditMode] = useState(false);
  const [deleteModal, openModal] = useState(false);

  return (
    <div className='d-flex'>
      {!isEditMode && (
        <>
          <CButton
            className='mt-1 mb-1 w-100'
            style={{
              borderBottomRightRadius: 0,
              borderTopRightRadius: 0
            }}
            onClick={() => {
              if (activeFileIndex !== tindex) resetChoices();
              setAddedTemplate(false);
              onClick(tindex);
            }}
          >
            {tmpFileName}
          </CButton>
          <CDropdown className='mt-1 mb-1'>
            <CDropdownToggle
              style={{
                borderBottomLeftRadius: 0,
                borderTopLeftRadius: 0
              }}
            />
            <CDropdownMenu>
              <CDropdownItem onClick={() => setIsEditMode(true)}>
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
            onRenameFile(event, tindex, filename, tmpFileName);
          }}
        >
          <CFormInput
            type="text"
            id="fileNameForm"
            defaultValue={tmpFileName}
            onChange={(event) => setTmpFileName(event.target.value)}
          />
          <CButton type='submit'>
            Save
          </CButton>
        </CForm>
      )}
      <CModal
        className="show"
        visible={deleteModal}
      >
        <CModalBody>
          Are you sure you want to delete this file?
        </CModalBody>
        <CModalFooter>
          <CButton color='secondary' onClick={() => openModal(false)}>Cancel</CButton>
          <CButton
            color='primary'
            onClick={(event) => {
              openModal(false);
              onDeleteFile(event, tmpFileName, tindex);
            }}
          >
            Yes
          </CButton>
        </CModalFooter>
      </CModal>
    </div>

  )
}

export default TemplateFileButton
