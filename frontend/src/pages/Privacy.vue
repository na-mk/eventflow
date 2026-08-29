<template>
  <section class="max-w-4xl mx-auto space-y-8">
    <div class="glass p-8 md:p-10">
      <div class="section-label mb-3">Vos données, vos choix</div>
      <h1 class="text-3xl md:text-4xl font-extrabold text-main">Politique de confidentialité</h1>
      <p class="mt-3 max-w-2xl text-base leading-7 text-sub">
        Cette page explique simplement quelles données EventFlow utilise, pourquoi elles sont nécessaires et comment exercer vos droits.
      </p>
      <div class="mt-5 inline-flex rounded-full border px-3 py-1 text-xs text-muted" style="border-color:var(--border)">
        Version 1.0 — En vigueur depuis le 30/03/2026
      </div>
    </div>

    <div class="grid gap-5">
      <section class="glass p-6 md:p-8">
        <h2 class="text-xl font-extrabold text-main">1. Responsable du traitement</h2>
        <p class="mt-3 text-sm leading-7 text-sub">
          EventFlow est responsable du traitement des données utilisées pour fournir la plateforme.
        </p>
        <p class="mt-2 text-sm text-sub">Contact pour la protection des données : <strong class="text-main">dpo@eventflow.local</strong></p>
      </section>

      <section class="glass p-6 md:p-8">
        <h2 class="text-xl font-extrabold text-main">2. Données collectées</h2>
        <p class="mt-3 text-sm leading-7 text-sub">Lors de votre inscription et de l'utilisation de votre compte, nous pouvons traiter :</p>
        <ul class="mt-4 grid gap-2 text-sm text-sub sm:grid-cols-2">
          <li class="rounded-xl border p-3" style="border-color:var(--border)">Nom et prénom</li>
          <li class="rounded-xl border p-3" style="border-color:var(--border)">Adresse email</li>
          <li class="rounded-xl border p-3" style="border-color:var(--border)">Numéro de téléphone, si renseigné</li>
          <li class="rounded-xl border p-3" style="border-color:var(--border)">Date et version du consentement</li>
          <li class="rounded-xl border p-3 sm:col-span-2" style="border-color:var(--border)">Adresse IP lors de certaines actions, stockée sous forme de hash SHA-256</li>
        </ul>
      </section>

      <section class="glass p-6 md:p-8">
        <h2 class="text-xl font-extrabold text-main">3. Pourquoi utilisons-nous ces données ?</h2>
        <p class="mt-3 text-sm leading-7 text-sub">
          Le traitement repose sur votre <strong class="text-main">consentement explicite</strong> (Art. 6.1.a du RGPD) recueilli lors de votre inscription.
        </p>
        <ul class="mt-4 list-disc ml-5 space-y-2 text-sm text-sub">
          <li>Gérer votre compte et votre authentification.</li>
          <li>Permettre l'inscription et le suivi de vos événements.</li>
          <li>Vous transmettre les informations utiles liées aux événements.</li>
          <li>Conserver les traces nécessaires à l'exercice et au suivi de vos droits.</li>
        </ul>
      </section>

      <section class="glass p-6 md:p-8">
        <h2 class="text-xl font-extrabold text-main">4. Durées de conservation</h2>
        <div class="mt-4 grid gap-3 text-sm text-sub">
          <div class="rounded-xl border p-4" style="border-color:var(--border)"><strong class="text-main">Données de compte :</strong> 24 mois après la dernière activité.</div>
          <div class="rounded-xl border p-4" style="border-color:var(--border)"><strong class="text-main">Logs de consentement :</strong> 5 ans.</div>
          <div class="rounded-xl border p-4" style="border-color:var(--border)"><strong class="text-main">Données anonymisées :</strong> conservation sans données directement identifiantes.</div>
        </div>
      </section>

      <section class="glass p-6 md:p-8">
        <h2 class="text-xl font-extrabold text-main">5. Vos droits</h2>
        <p class="mt-3 text-sm leading-7 text-sub">Vous gardez le contrôle de vos données. Depuis votre espace, vous pouvez notamment :</p>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
          <div class="rounded-xl border p-4" style="border-color:var(--border)"><div class="font-bold text-main">Accès</div><p class="mt-1 text-sm text-sub">Consulter les données associées à votre compte.</p></div>
          <div class="rounded-xl border p-4" style="border-color:var(--border)"><div class="font-bold text-main">Rectification</div><p class="mt-1 text-sm text-sub">Mettre à jour vos informations personnelles.</p></div>
          <div class="rounded-xl border p-4" style="border-color:var(--border)"><div class="font-bold text-main">Portabilité</div><p class="mt-1 text-sm text-sub">Exporter vos données dans un format exploitable.</p></div>
          <div class="rounded-xl border p-4" style="border-color:var(--border)"><div class="font-bold text-main">Effacement</div><p class="mt-1 text-sm text-sub">Demander l'anonymisation de votre compte.</p></div>
        </div>
        <p class="mt-5 text-sm text-sub">
          Ces droits correspondent notamment aux articles 15, 16, 17, 20 et 21 du RGPD.
          <router-link to="/profile" class="font-semibold text-orange-500 hover:underline">Accéder à Mes données</router-link>
          ou contactez dpo@eventflow.local.
        </p>
      </section>

      <section class="glass p-6 md:p-8">
        <h2 class="text-xl font-extrabold text-main">6. Sécurité</h2>
        <p class="mt-3 text-sm leading-7 text-sub">
          EventFlow applique des mesures de sécurité pour limiter l'accès non autorisé aux comptes et aux données personnelles. Les mots de passe ne sont pas stockés en clair et les sessions d'authentification ont une durée limitée.
        </p>
      </section>
    </div>
  </section>
</template>