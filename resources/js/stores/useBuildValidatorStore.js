import { defineStore } from 'pinia';
import axios from 'axios';
import { ref } from 'vue';

export const useBuildValidatorStore = defineStore('buildValidator', () => {
  const errors = ref([]);
  const warnings = ref([]);
  const validating = ref(false);
  const lastRequest = ref(null);

  async function validateBuild(componentIds) {
    if (lastRequest.value) {
      clearTimeout(lastRequest.value);
    }
    lastRequest.value = setTimeout(async () => {
      validating.value = true;
      errors.value = [];
      warnings.value = [];
      try {
        const response = await axios.post('/api/builds/validate-temp', {
          component_ids: componentIds,
        });
        errors.value = response.data.errors || [];
        warnings.value = response.data.warnings || [];
      } catch (e) {
        errors.value = ['Erreur serveur lors de la validation.'];
      } finally {
        validating.value = false;
      }
    }, 300);
  }

  return { errors, warnings, validating, validateBuild };
});
