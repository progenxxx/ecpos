import{d as c,G as _,k as X,S as Q,c as Z,w as f,o as v,b as d,e as g,a as i,f as N,t as R,n as K,F as ee,h as te,g as M,u as oe,C as se}from"./app-CacuESpm.js";import{P as $,D as ne}from"./dataTables-BnFpA0RH.js";import{C as ae,G as ie,_ as le}from"./CopyFrom-CtTBASzZ.js";import{_ as E}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-Oe7tEmQB.js";import{T as re}from"./TableContainer-D6l808Wc.js";import{_ as ue}from"./Main-2jZhlWuC.js";import{S as de}from"./Save-CFLLzL9K.js";import{B as ce}from"./Back-CPB2HwX9.js";import{C as pe}from"./Cart-CNbd93Qn.js";import{_ as me}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./Modal-CTDQd9c8.js";import"./InputError-DlCP3Rcd.js";import"./Logout-CqgzcP31.js";/* empty css                                                             */import"./PartyCake143-Di4gRlu7.js";import"./Attendance-B0jvDPeG.js";const fe={key:0,class:"fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-filter backdrop-blur-sm"},ve={class:"mb-4 flex justify-between items-center px-10"},be={class:"flex space-x-2"},ge={class:"relative column-visibility-dropdown"},Te={key:0,class:"absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"},ye={class:"py-1",role:"menu"},he=["checked","onChange"],Ce={class:"flex space-x-2"},Ee={class:"inline-flex items-center"},we=["checked","onChange"],xe={__name:"index copy 3",props:{stockcountingtrans:{type:Array,required:!0},journalid:{type:[String,Number],required:!0},items:{type:Array,required:!0},isPosted:{type:Number,required:!0}},setup(b){$.use(ne);const A=b,T=c(""),w=c(!1),D=c(!1),x=c(!1),k=c(!1),S=c([]),p=_({}),l=_({text:"",type:"",timeout:null}),y=c(!1),r=c({ITEMID:!0,itemname:!0,itemgroup:!0,ADJUSTMENT:!0,RECEIVEDCOUNT:!0,VARIANCE:!0,TRANSFERCOUNT:!0,WASTECOUNT:!0,WASTETYPE:!0,COUNTED:!0}),m=c(null),h=(e,o,t=3e3)=>{l.timeout&&clearTimeout(l.timeout),l.text=e,l.type=o,l.timeout=setTimeout(()=>{l.text="",l.type="",l.timeout=null},t)},C=c([{data:"ITEMID",title:"ITEMID",width:"120px",visible:r.value.ITEMID},{data:"itemname",title:"ITEMNAME",width:"200px",visible:r.value.itemname},{data:"itemgroup",title:"CATEGORY",width:"150px",visible:r.value.itemgroup},{data:"ADJUSTMENT",title:"ORDER",width:"100px",visible:r.value.ADJUSTMENT,render:function(e,o,t){return o==="display"?`
                    <input type="number" 
                        class="counted-input form-input w-full rounded-md"
                        value="${Number(e).toFixed(0)}"
                        min="0"
                        data-field="ADJUSTMENT"
                        disabled
                        style="opacity: 0.6;"
                    >
                `:e}},{data:"RECEIVEDCOUNT",title:"ACTUAL RECEIVED",width:"120px",visible:r.value.RECEIVEDCOUNT,render:function(e,o,t){if(o==="display"){const s=Number(e),n=new Date,a=n.getHours(),u=t.TRANSDATE===n.toISOString().split("T")[0],O=a>=12||a===0,U=t.posted===1||!u||O;return`
                    <input type="number" 
                        class="counted-input form-input w-full rounded-md"
                        value="${s.toFixed(0)}"
                        min="0"
                        data-field="RECEIVEDCOUNT"
                        ${U?"disabled":""}
                        style="${U?"opacity: 0.6;":""}"
                        title="${O?"Receiving is disabled between 12 PM and 12 AM":""}"
                    >
                `}return e}},{data:null,title:"VARIANCE",width:"100px",render:function(e,o,t){if(o==="display"){const s=Number(t.ADJUSTMENT),n=Number(t.RECEIVEDCOUNT),a=s-n;return`
                    <div class="text-center p-2" 
                         style="background-color: ${a===0?"#f3f3f3":a<0?"#ffebee":"#e8f5e9"}">
                        ${a}
                    </div>
                `}return""}},{data:"TRANSFERCOUNT",title:"TRANSFER",width:"120px",visible:r.value.TRANSFERCOUNT,render:function(e,o,t){if(o==="display"){const s=Number(e),n=new Date().toISOString().split("T")[0],a=t.TRANSDATE===n,u=t.posted===1||!a;return`
                    <input type="number" 
                        class="counted-input form-input w-full rounded-md"
                        value="${s.toFixed(0)}"
                        min="0"
                        data-field="TRANSFERCOUNT"
                        ${u?"disabled":""}
                        style="${u?"opacity: 0.6;":""}"
                    >
                `}return e}},{data:"WASTECOUNT",title:"WASTE COUNT",width:"120px",visible:r.value.WASTECOUNT,render:function(e,o,t){if(o==="display"){const s=Number(e),n=t.posted===1||t.WASTETYPE!==null;return`
                    <input type="number"
                        class="counted-input form-input w-full rounded-md"
                        value="${s.toFixed(0)}"
                        min="0"
                        data-field="WASTECOUNT"
                        ${n?"disabled":""}
                        style="${n?"opacity: 0.6;":""}"
                    >
                `}return e}},{data:"WASTETYPE",title:"WASTE TYPE",width:"150px",visible:r.value.WASTETYPE,render:function(e,o,t){if(o==="display"){const s=t.posted===1||e!==null,n=["throw_away","early_molds","pull_out","rat_bites","ant_bites"],a=e||"";return`
                    <select 
                        class="waste-type-select form-select w-full rounded-md"
                        data-field="WASTETYPE"
                        ${s?"disabled":""}>
                        <option value="">Select type</option>
                        ${n.map(u=>`
                            <option value="${u}" ${a===u?"selected":""}>
                                ${u.replace(/_/g," ")}
                            </option>
                        `).join("")}
                    </select>
                `}return e}},{data:"COUNTED",title:"ACTUAL COUNT",width:"120px",visible:r.value.COUNTED,render:function(e,o,t){if(o==="display"){const s=Number(e),n=t.TRANSDATE===new Date().toISOString().split("T")[0],a=t.posted===1||!n;return`
                    <input type="number"
                        class="counted-input form-input w-full rounded-md"
                        value="${s.toFixed(0)}"
                        min="0"
                        data-field="COUNTED"
                        ${a?"disabled":""}
                        style="${a?"opacity: 0.6;":""}"
                    >
                `}return e}}]),V={paging:!1,scrollX:!0,scrollY:"70vh",scrollCollapse:!0,responsive:!0,processing:!0,stateSave:!0,columns:C.value,language:{processing:"Loading..."},initComplete:function(e,o){m.value&&(m.value.dtInstance=this.api())},drawCallback:function(e){this.api().rows().every(function(){const t=this.data();this.node().querySelectorAll(".counted-input, .waste-type-select").forEach(a=>{t.posted||a.addEventListener("change",u=>F(u,t))})})}},L=e=>{r.value[e]=!r.value[e];const o=C.value.find(t=>t.data===e);if(o&&(o.visible=r.value[e]),m.value)try{const t=m.value.dt;if(t){const s=C.value.findIndex(n=>n.data===e);s!==-1&&t.column(s).visible(r.value[e])}}catch(t){console.error("Error updating column visibility:",t)}},W=()=>{y.value=!y.value},I=e=>{const o=document.querySelector(".column-visibility-dropdown");o&&!o.contains(e.target)&&(y.value=!1)},F=(e,o)=>{const t=e.target.dataset.field;if(!t||o.posted)return;p[o.ITEMID]||(p[o.ITEMID]={});const s=e.target.type==="number"?parseFloat(e.target.value)||0:e.target.value;if(p[o.ITEMID][t]=s,e.target.type==="number"){const n=s===0?"#f3f3f3":"white";e.target.style.backgroundColor=n}},j=async()=>{var e,o;try{if(Object.keys(p).length===0){h("No changes to update","info");return}w.value=!0,h("Updating values...","info");const t=await se.post("/api/stock-counting-line/update-all-counted-values",{journalId:A.journalid,updatedValues:p});t.data.success&&(h(t.data.message,"success"),Object.entries(p).forEach(([s,n])=>{const a=S.value.find(u=>u.ITEMID===s);a&&Object.assign(a,n)}),Object.keys(p).forEach(s=>delete p[s]),window.location.reload())}catch(t){console.error("Update error:",t),h(((o=(e=t.response)==null?void 0:e.data)==null?void 0:o.message)||"Update failed","error")}finally{w.value=!1}},B=()=>{window.location.href="/StockCounting"},J=e=>{window.location.href=`/ViewStockCountingLine/${e}`},P=e=>{T.value=e,x.value=!0},G=()=>{k.value=!1},H=()=>{x.value=!1},Y=()=>{D.value=!1},z=e=>{console.log("Selected Item:",e)},q=()=>{if(m.value&&m.value.dt){const e=m.value.dt;window.dataTableInstance=e}};return X(()=>{S.value=A.stockcountingtrans,document.addEventListener("click",I),setTimeout(q,0)}),Q(()=>{document.removeEventListener("click",I),l.timeout&&clearTimeout(l.timeout)}),(e,o)=>(v(),Z(ue,{"active-tab":"STOCK"},{modals:f(()=>[d(ae,{"show-modal":k.value,JOURNALID:T.value,items:b.items,onToggleActive:G,onSelectItem:z},null,8,["show-modal","JOURNALID","items"]),d(ie,{"show-modal":x.value,JOURNALID:T.value,onToggleActive:H},null,8,["show-modal","JOURNALID"]),d(le,{"show-modal":D.value,JOURNALID:T.value,onToggleActive:Y},null,8,["show-modal","JOURNALID"])]),main:f(()=>[d(re,null,{default:f(()=>[w.value?(v(),g("div",fe,o[2]||(o[2]=[i("div",{class:"bg-white p-4 rounded-lg shadow-lg flex items-center space-x-3"},[i("svg",{class:"animate-spin h-5 w-5 text-navy",xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24"},[i("circle",{class:"opacity-25",cx:"12",cy:"12",r:"10",stroke:"currentColor","stroke-width":"4"}),i("path",{class:"opacity-75",fill:"currentColor",d:"M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"})]),i("span",{class:"text-gray-700"},"Processing...")],-1)]))):N("",!0),l.text?(v(),g("div",{key:1,class:K(["fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg transition-all duration-300",l.type==="success"?"bg-green-100 text-green-800 border border-green-300":l.type==="error"?"bg-red-100 text-red-800 border border-red-300":"bg-blue-100 text-blue-800 border border-blue-300"])},R(l.text),3)):N("",!0),i("div",ve,[i("div",be,[i("div",ge,[i("button",{onClick:W,class:"bg-navy px-4 py-2 rounded-md text-white flex items-center space-x-2"},o[3]||(o[3]=[i("span",null,"Show/Hide Columns",-1),i("svg",{class:"w-4 h-4",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[i("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M19 9l-7 7-7-7"})],-1)])),y.value?(v(),g("div",Te,[i("div",ye,[(v(!0),g(ee,null,te(r.value,(t,s)=>(v(),g("label",{key:s,class:"flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"},[i("input",{type:"checkbox",checked:t,onChange:n=>L(s),class:"mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"},null,40,he),M(" "+R(s),1)]))),128))])])):N("",!0)]),d(E,{onClick:B,class:"bg-navy px-4 py-2"},{default:f(()=>[d(ce,{class:"h-5 w-5"})]),_:1}),d(E,{onClick:o[0]||(o[0]=t=>P(b.journalid)),class:"bg-navy px-4 py-2"},{default:f(()=>o[4]||(o[4]=[M(" GENERATE ")])),_:1}),d(E,{onClick:o[1]||(o[1]=t=>J(b.journalid)),class:"bg-navy px-4 py-2"},{default:f(()=>[d(pe,{class:"h-5 w-5"})]),_:1})]),i("div",Ce,[d(E,{onClick:j,class:"bg-navy px-4 py-2"},{default:f(()=>[d(de,{class:"h-5 w-5"})]),_:1})])]),d(oe($),{ref_key:"dataTableRef",ref:m,data:b.stockcountingtrans,columns:C.value,class:"w-full display nowrap",options:V},{action:f(t=>[i("label",Ee,[i("input",{type:"checkbox",class:"form-checkbox h-5 w-5 text-blue-600 rounded",checked:t.selected,onChange:s=>e.toggleRowSelection(t)},null,40,we)])]),_:1},8,["data","columns"])]),_:1})]),_:1}))}},je=me(xe,[["__scopeId","data-v-af1fe42d"]]);export{je as default};
