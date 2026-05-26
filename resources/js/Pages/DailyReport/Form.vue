<script setup>
import AppMain from '@/Layouts/AppMain.vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import ButtonColor from '@/Components/ButtonColor.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import SelectInput from '@/Components/SelectInput.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { computed, onMounted, ref } from 'vue'

// Props desde backend
// dailyReport puede ser null si estamos creando
const props = defineProps({
    dailyReport: Object,
    projects: Array,
    machines: Array,
    lastReport: Object,
    maintenanceTypes: { type:Array, default:()=>[] }
})

const user = usePage().props.auth.user


// ==========================================================================
// Mantenciones
// ==========================================================================

// Mapa id → tipo para acceso rápido en template
// Evita búsquedas repetidas dentro del render
const maintenanceTypeMap = computed(() =>
    Object.fromEntries(props.maintenanceTypes.map(t => [t.id, t]))
)

// Genera estructura base de mantenciones
// Siempre recorremos todos los tipos para mantener consistencia
const buildMaintenances = () => {
    return props.maintenanceTypes.map(type => {

        const existing = props.dailyReport?.maintenances?.find(
            m => m.maintenance_type_id === type.id
        )

        return {
            maintenance_type_id: type.id,
            quantity: existing?.quantity ?? null,
            observation: existing?.observation ?? ''
        }
    })
}


// Anomalías

// Precarga anomalías si estamos editando
// Separamos fotos existentes de las nuevas (File objects)
const buildAnomalies = () => {

    if (!props.dailyReport?.anomalies?.length) return []

    return props.dailyReport.anomalies.map(a => ({
        id: a.id,
        temp_id: crypto.randomUUID(),
        description: a.description ?? '',
        severity: a.severity ?? '',
        existing_photos: a.media?.map(p => p.id) ?? [],
        media: a.media ?? [],
        photos: [] // fotos nuevas a subir

    }))
}


// Inicialización del formulario

// Reglas:
// - Crear → usar datos del último reporte si existen
// - Editar → priorizar datos actuales
const form = useForm({
    project_id: props.dailyReport?.project_id ?? props.lastReport?.project_id ?? null,
    machine_id: props.dailyReport?.machine_id ?? props.lastReport?.machine_id ?? null,
    date: props.dailyReport?.date ?? props.lastReport?.date ?? '',
    initial_km: props.dailyReport?.initial_km ?? props.lastReport?.final_km ?? '',
    final_km: props.dailyReport?.final_km ?? '',
    initial_hm: props.dailyReport?.initial_hm ?? props.lastReport?.final_hm ?? '',
    final_hm: props.dailyReport?.final_hm ?? '',
    work_description: props.dailyReport?.work_description ?? '',
    finished_at: props.dailyReport?.finished_at ?? null,
    is_finished: false,
    maintenances: buildMaintenances(),
    anomalies: buildAnomalies()
})


// ==========================================================================
// Cálculos derivados (no persistentes)
// ==========================================================================

const totalKm = computed(() =>
    form.final_km > 0 && form.initial_km > 0
        ? form.final_km - form.initial_km
        : ''
)

const totalHm = computed(() =>
    form.final_hm > 0 && form.initial_hm > 0
        ? form.final_hm - form.initial_hm
        : ''
)


// Finalizar reporte

// Marca el reporte como finalizado
// Se envía como update normal con flag adicional
const finishReport = () => {
    form.transform(data => ({
        ...data,
        is_finished: true
    }))
    .put(route('daily-reports.update', props.dailyReport.id), {
        forceFormData: true
    })
}


// Manejo offline 

// Estrategia simple:
// - Sin conexión → guardar en localStorage
// - Cuando vuelve internet → reenviar
// Nota: no hay deduplicación ni control de errores avanzado

const OFFLINE_KEY = "offline_daily_reports"

function saveOffline(data){
    const existing = JSON.parse(localStorage.getItem(OFFLINE_KEY) || "[]")
    existing.push(data)
    localStorage.setItem(OFFLINE_KEY, JSON.stringify(existing))

    alert("Sin conexión. El reporte se enviará automáticamente cuando vuelva internet.")
}

function trySendOffline(){

    if(!navigator.onLine) return

    const stored = JSON.parse(localStorage.getItem(OFFLINE_KEY) || "[]")
    if(!stored.length) return

    stored.forEach(r => {
        router.post(route('daily-reports.store'), r)
    })

    localStorage.removeItem(OFFLINE_KEY)
}

// Escuchamos evento online al montar componente
onMounted(() => {
    window.addEventListener('online', trySendOffline)
    trySendOffline()
})


// Submit principal

const submit = () => {

    if (!navigator.onLine) {
        saveOffline(form.data())
        return
    }



    const options = { forceFormData: true }

    if (props.dailyReport) {
        form.put(route('daily-reports.update', props.dailyReport.id), options)
    } else {
        form.post(route('daily-reports.store'), options)
    }
}
// Gestión de anomalías

function addAnomaly(){
    form.anomalies.push({
        id: null,
        temp_id: crypto.randomUUID(),
        description: '',
        severity: '',
        existing_photos: [],
        media: [],
        photos: []
    })
}

function removeAnomaly(index){
    form.anomalies.splice(index, 1)
}
// Manejo de fotos
function handlePhotos(event, index){

    const fileInputs = event.target.files
    const maxDimension = 1280 // puedes ajustar (480 es muy chico para reportes)

    for (let i = 0; i < fileInputs.length; i++) {

        const file = fileInputs[i]
        const img = new Image()

        img.onload = () => {

            const canvas = document.createElement('canvas')
            const width = img.width
            const height = img.height

            let newWidth, newHeight

            if (width > height) {
                newWidth = maxDimension
                newHeight = (height / width) * maxDimension
            } else {
                newHeight = maxDimension
                newWidth = (width / height) * maxDimension
            }

            canvas.width = newWidth
            canvas.height = newHeight

            const ctx = canvas.getContext('2d')
            ctx.drawImage(img, 0, 0, newWidth, newHeight)

            const dataURL = canvas.toDataURL('image/jpeg', 0.8)
            const optimizedFile = dataURLtoFile(dataURL, file.name)

            if(!form.anomalies[index].photos){
                form.anomalies[index].photos = []
            }

            form.anomalies[index].photos.push(optimizedFile)
        }

        img.src = URL.createObjectURL(file)
    }
}

// Convierte un dataURL a un objeto File para subir al backend
function dataURLtoFile(dataurl, filename){
    const arr = dataurl.split(',')
    const mime = arr[0].match(/:(.*?);/)[1]
    const bstr = atob(arr[1])
    let n = bstr.length
    const u8arr = new Uint8Array(n)

    while (n--) {
        u8arr[n] = bstr.charCodeAt(n)
    }

    return new File([u8arr], filename, { type: mime })
}
// Elimina una foto nueva (aún no guardada)
function removePhoto(aIndex, pIndex){
    form.anomalies[aIndex].photos.splice(pIndex, 1)
}
// Elimina una foto existente (ya guardada en backend)
function removeExistingPhoto(aIndex, photoId){

    const anomaly = form.anomalies[aIndex]

    anomaly.existing_photos =
        anomaly.existing_photos.filter(id => id !== photoId)

    anomaly.media =
        anomaly.media.filter(p => p.id !== photoId)
}
// Crea una URL temporal para mostrar preview de fotos nuevas
function createImageURL(file){
    return URL.createObjectURL(file)
}
</script>
<template>
<AppMain>
    <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800 text-center">
            {{ props.dailyReport ? 'Editar Reporte Diario' : 'Crear Reporte Diario' }}
        </h2>
    </template>

    <div class="max-w-4xl mx-auto mt-6 p-6 bg-white shadow-md rounded">

        <!-- Logo y Número -->
        <div class="grid grid-cols-2 gap-2 mb-4">
            <ApplicationLogo class="mx-auto w-auto h-20" />
            <div class="text-center text-green-600 font-bold text-lg self-center">
                {{ props.dailyReport ? 'Nº ' + props.dailyReport.id : 'Sin Guardar' }}
            </div>
        </div>

        <h2 class="text-center text-gray-800 font-bold text-2xl mb-6">
            REPORTE DIARIO DE MAQUINARIA
        </h2>

        <form @submit.prevent="submit" class="flex flex-col gap-2 md:grid md:grid-cols-2 md:gap-4">

            <!-- Usuario -->
            <div>
                <InputLabel value="Usuario" />
                <TextInput v-model="user.name" class="w-full bg-gray-100" disabled />
            </div>

            <!-- Obra -->
            <div>
                <InputLabel value="Obra" />
                <SelectInput v-model="form.project_id" class="w-full">
                    <option :value="null" disabled>Seleccione una Obra</option>
                    <option v-for="p in props.projects" :key="p.id" :value="p.id">
                        {{ p.name }}
                    </option>
                </SelectInput>
                <InputError :message="form.errors.project_id" />
            </div>

            <!-- Máquina -->
            <div>
                <InputLabel value="Maquinaria" />
                <SelectInput v-model="form.machine_id" class="w-full">
                    <option :value="null" disabled>Seleccione una maquinaria</option>
                    <option v-for="m in props.machines" :key="m.id" :value="m.id">
                        {{ m.plate }} - {{ m.internal_id }}
                    </option>
                </SelectInput>
                <InputError :message="form.errors.machine_id" />
            </div>

            <!-- Fecha -->
            <div>
                <InputLabel value="Fecha" />
                <TextInput type="date" v-model="form.date"/>
                <InputError :message="form.errors.date" />
            </div>

            <!-- KM -->
                <div class="border rounded">
                    <div class="col-span-3 text-center text-gray-800 text-sm font-bold">
                        INDICAR KILOMETRAJE
                    </div>
                    <div class="grid grid-cols-3 gap-2 p-2">
                        <div>
                            <InputLabel value="Inicial" />
                            <div class="input-withend">
                                <TextInput type="number" v-model="form.initial_km" class="w-full"/>
                                <div class="input-end">
                                    Km
                                </div>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Final" />
                            <div class="input-withend">
                                <TextInput type="number" v-model="form.final_km"  class="w-full"/>
                                <div class="input-end">
                                    Km
                                </div>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Total" />
                            <div class="input-withend">
                                <TextInput type="number" v-model="totalKm" class="w-full bg-gray-100" readonly />
                                <div class="input-end">
                                    Km
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            <!-- HM -->
                <div class="border rounded">
                    <div class="col-span-3 text-center text-gray-800 text-sm font-bold">
                        INDICAR HOROMETRO
                    </div>
                    <div class="grid grid-cols-3 gap-2 p-2">
                        <div>
                            <InputLabel value="Inicial" />
                            <div class="input-withend">
                                <TextInput type="number" v-model="form.initial_hm" class="w-full"/>
                                <div class="input-end">
                                    Hr
                                </div>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Final" />
                            <div class="input-withend">
                                <TextInput type="number" v-model="form.final_hm" class="w-full"/>
                                <div class="input-end">
                                    Hr
                                </div>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Total" />
                            <div class="input-withend">
                                <TextInput type="number" v-model="totalHm" class="w-full bg-gray-100" :class="totalHm<0 ? 'text-danger-600' : ''" readonly />
                                <div class="input-end">
                                    Hr
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Trabajo -->
            <div class="col-span-2">
                <h3 class="text-center text-gray-800 text-sm font-bold">
                    DESCRIPCION DE LOS TRABAJOS REALIZADOS
                </h3>
                <textarea
                    v-model="form.work_description"
                    class="w-full min-h-32 border-stone-300 focus:border-stone-500 focus:ring-stone-500 rounded-md shadow-sm">
                </textarea>
            </div>

            <!-- DETALLE MANTENCIONES -->
            <div class="col-span-2 mt-6">

                <h3 class="text-center text-gray-800 text-sm font-bold">
                    DETALLE A LLENAR PARA TODAS LAS MAQUINAS
                </h3>

                <div class="overflow-x-auto">

                    <table class="w-full border border-gray-300 text-sm table-fixed">

                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">Tipo de Mantención</th>
                                <th class="border px-3 py-2 text-left">Cantidad</th>
                                <th class="border px-3 py-2 text-left">Observación</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr
                            v-for="(maintenance,index) in form.maintenances"
                            :key="maintenance.maintenance_type_id"
                            :class="[
                                index%2 ? 'bg-gray-50' : '',
                                maintenance.quantity || maintenance.observation
                                ? 'bg-blue-50'
                                : ''
                            ]"
                            >

                                <!-- NOMBRE + UNIDAD -->
                                <td class="border px-3 py-2 font-medium align-middle">
                                    {{ maintenanceTypeMap[maintenance.maintenance_type_id]?.name }}
                                </td>


                                <!-- CANTIDAD -->
                                <td class="border px-3 py-2">

                                    <div class="input-withend" v-if="maintenanceTypeMap[maintenance.maintenance_type_id]?.requires_quantity">
                                    <TextInput
                                    type="number"
                                    step="0.01"
                                    v-model.number="maintenance.quantity"
                                    class="w-full"
                                    />
                                        <div class="input-end" v-if="maintenanceTypeMap[maintenance.maintenance_type_id]?.unit">
                                            {{ maintenanceTypeMap[maintenance.maintenance_type_id]?.unit }}
                                        </div>
                                    </div>

                                    <span v-else class="text-gray-500 italic">
                                        No aplica
                                    </span>

                                </td>
                                <!-- OBSERVACION -->
                                <td class="border px-3 py-2">

                                    <TextInput
                                    v-model="maintenance.observation"
                                    class="w-full"
                                    placeholder="Observaciones (opcional)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- ANOMALIAS -->
            <div class="col-span-2 mt-8">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-center text-gray-800 text-sm font-bold">
                        ANOMALÍAS DETECTADAS
                    </h3>

                    <ButtonColor type="button" color="orange" @click="addAnomaly">
                        + Agregar anomalía
                    </ButtonColor>
                </div>

                <div v-for="(anomaly, i) in form.anomalies" :key="anomaly.id || anomaly.temp_id" >
                    <div class="grid grid-cols-2 gap-3 border rounded p-3 mb-4 bg-gray-50">
                        <!-- descripción -->
                        <div class="col-span-2">
                            <div class="flex ">
                                <InputLabel value="Descripción anomalía" class="flex-1"/>
                                <!-- eliminar anomalía -->
                                <div class="col-span-2">
                                    <ButtonColor type="button" color="red" @click="removeAnomaly(i)" padding="py-0 px-1">
                                        Eliminar anomalía
                                    </ButtonColor>
                                </div>
                            </div>
                            <textarea
                                v-model="anomaly.description"
                                class="w-full rounded border-gray-300"
                            />
                        </div>

                        <!-- gravedad -->
                        <div>
                            <InputLabel value="Gravedad" />
                            <SelectInput v-model="anomaly.severity">
                                <option value="">Seleccione</option>
                                <option value="leve">Leve</option>
                                <option value="media">Media</option>
                                <option value="critica">Crítica</option>
                            </SelectInput>
                        </div>

                        <!-- subir fotos -->
                        <div>
                            <InputLabel value="Fotos anomalía" />
                            <input
                                type="file"
                                multiple
                                accept="image/*"
                                capture="environment"
                                @change="handlePhotos($event, i)"
                                class="block w-full text-sm"
                            />
                        </div>

                        <!-- fotos guardadas -->
                        <div
                            v-for="photo in anomaly.media"
                            :key="'existing-'+photo.id"
                            class="relative"
                        >
                            <!-- Usamos URL original para mostrar imagen guardada en backend -->
                            <img
                                :src="photo.original_url"
                                class="w-24 h-24 object-cover rounded border"
                            />
                            <button
                                type="button"
                                class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full px-2"
                                @click="removeExistingPhoto(i, photo.id)"
                            >
                                ×
                            </button>
                        </div>

                        <div class="col-span-2 flex gap-2 mt-2 flex-wrap">
                            <!-- fotos nuevas -->
                            <div
                                v-for="(photo, pIndex) in anomaly.photos"
                                :key="'new-'+pIndex"
                                class="relative w-24 h-24"
                            >
                                <img
                                    :src="createImageURL(photo)"
                                    class="w-24 h-24 object-cover rounded border"
                                />
                                <button
                                    type="button"
                                    class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full px-2"
                                    @click="removePhoto(i, pIndex)"
                                >
                                    ×
                                </button>
                            </div>
                        </div>


                    </div>
                </div>

                <div>
                    <span v-if="!form.anomalies.length" class="text-gray-500 italic">
                        No se han agregado anomalías.
                    </span>
                </div>

            </div>
            <hr class="mt-6 col-span-2" />
            <!-- Botones -->
            <div class="col-span-2 flex justify-between">
                <ButtonColor type="submit" color="green" :disabled="form.processing">
                    {{ props.dailyReport ? 'Actualizar Reporte' : 'Crear Reporte' }}
                </ButtonColor>

                <ButtonColor
                    v-if="props.dailyReport"
                    color="blue"
                    :disabled="form.is_finished || form.processing"
                    @click="finishReport">
                    Terminar Reporte
                </ButtonColor>
            </div>

        </form>
    </div>
</AppMain>
</template>
<style scoped>
/* Estilos específicos para este componente */
.input-withend {
    display: flex;
    align-items: stretch;
}

.input-withend input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    padding-right: 0.3rem; /* reduce padding para compensar el espacio del "Km" */
    padding-left: 0.3rem; /* padding normal a la izquierda */
}
.input-withend .input-end {
    border-left: none;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f9fafb; /* mismo fondo que el input */
    border-top-right-radius: 0.375rem; /* mismo border-radius que el input */
    border-bottom-right-radius: 0.375rem;
    border: 1px solid #d1d5db; /* mismo borde que el input */
    border-left: none; /* elimina borde izquierdo para unir con el input */
    padding: 0rem 0.35rem; /* mismo padding que el input */
}
</style>