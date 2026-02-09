<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  machine: Object,
  types: Array,
})

const form = useForm({
  internal_id: props.machine?.internal_id ?? '',
  plate: props.machine?.plate ?? '',
  name: props.machine?.name ?? '',
  machine_type_id: props.machine?.machine_type_id ?? '',
  fuel_type: props.machine?.fuel_type ?? '',
  fuel_capacity: props.machine?.fuel_capacity ?? '',
})

const submit = () => {
  props.machine
    ? form.put(route('machines.update', props.machine.id))
    : form.post(route('machines.store'))
}
</script>
