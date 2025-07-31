import { useState } from 'react';

export const useFormField = (initialValue: string = '') => {
  const [value, setValue] = useState(initialValue);
  const [characterCount, setCharacterCount] = useState(initialValue.length);

  const onChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setValue(e.target.value);
    setCharacterCount(e.target.value.length);
  };

  return { value, characterCount, onChange, setValue, setCharacterCount };
};
